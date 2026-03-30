<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\Appointment;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\UserRequest;
use App\Models\Vaccination;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TelegramAlertNotification;

class StaffController extends Controller
{
    use \App\Traits\AppointmentValidation;
    use \App\Traits\NotificationHelper;

    public function dashboard()
    {
        return view('staff.dashboard', [
            'appointmentsToday' => Appointment::whereDate('appointment_date', today())
                ->whereIn('status', ['approved', 'checked-in'])
                ->get(),
            'dueForVaccination' => Pet::notDeceased()->where('status', 'needs_booster')->limit(5)->get(),
            'recentVaccinations' => Vaccination::whereHas('pet', function ($q) {
                $q->notDeceased();
            })->with('pet')->latest()->limit(5)->get(),

            'owners' => User::where('role', 'owner')->with('pets')->orderBy('name')->get()
        ]);
    }

    public function appointments(Request $request)
    {
        $view = $request->get('view', 'today');
        // --- AUTOMATIC STATUS UPDATER ---
        // We only fetch appointments that are NOT yet checked-in or finished
        $activeToday = Appointment::whereDate('appointment_date', today())
            ->whereIn('status', ['approved', 'pending', 'rescheduled', 'late'])
            ->get();

        foreach ($activeToday as $apt) {
            $scheduledTime = \Carbon\Carbon::parse($apt->appointment_date . ' ' . $apt->appointment_time);
            $minutesDiff = now()->diffInMinutes($scheduledTime, false);

            // 1. Mark as MISSED
            // Logic: 30 mins (slot) + 15 mins (grace) = 45 mins total after the start time.
            if ($minutesDiff < -45) {
                $apt->update(['status' => 'missed']);
            }
            // 2. Mark as LATE
            // Logic: 1 minute past the start time (e.g., 10:31 for a 10:30 slot)
            elseif ($minutesDiff < 0 && $apt->status !== 'late') {
                $apt->update(['status' => 'late']);
            }
        }
        $query = Appointment::with(['user', 'pet']);

        $appointments = match ($view) {
            'upcoming' => $query->where('appointment_date', '>', today())
                                ->whereIn('status', ['approved', 'rescheduled', 'pending']), // Include pending in upcoming

            'completed' => $query->whereIn('status', ['done', 'completed']),

            'missed' => $query->whereIn('status', ['missed']),

            // Today's view: Included 'pending' so staff can see new requests immediately
            default => $query->whereDate('appointment_date', today())
                                ->whereIn('status', ['pending', 'approved', 'checked-in', 'late', 'rescheduled']),
        };

        $paginatedAppointments = $appointments->orderBy('appointment_date')
                             ->orderBy('appointment_time')
                             ->paginate(10)
                             ->appends(['view' => $view]);

        return view('staff.appointments', [
            'appointments' => $paginatedAppointments,
            'view' => $view,
            'owners' => User::where('role', 'owner')->with('pets')->orderBy('name')->get()
        ]);
    }

    public function updateAppointmentStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|string']);

        $newStatus = $request->status;
        $appointment = Appointment::findOrFail($id);
        $checkStatus = strtolower($newStatus);

        // ---  NEW LOGIC: Specifically handle Approval ---
        if ($checkStatus === 'approved') {
            $appointment->status = 'approved';
            $appointment->save();

            // Trigger the specific approval notification
            $this->sendTelegramNotification($appointment, 'appointment_approved');

            return back()->with('success', "Appointment approved successfully!");
        }

        if (in_array($checkStatus, ['done', 'completed'])) {
            if ($appointment->status === 'completed' || $appointment->status === 'Done') {
                return back()->with('info', 'This appointment is already processed.');
            }

            $appointment->status = 'completed';
            $appointment->administered_by = auth()->user()->name;

            $serviceType = $appointment->service_type;
            $finalName = $appointment->vaccine_name ?? $serviceType;
            $batchNo = 'AUTO-' . date('Ymd');

            // Separate Vaccinations from General Services
            $vaccines = ['Anti-Rabies', '5-in-1', '4-in-1 (Cat)', 'Vaccination'];
            $otherServices = ['Deworming', 'Check-up', 'Kapon'];

            if ($this->isVaccinationService($appointment->service_type)) {
                $pet = Pet::find($appointment->pet_id);

                if ($pet) {
                    $isVaccine = in_array($serviceType, $vaccines);
                    $isSurgical = (strtolower($serviceType) === 'kapon');

                    // Get interval only for vaccines and deworming
                    $interval = ($isSurgical || $serviceType === 'Check-up') ? 0 : $this->getServiceInterval($finalName);

                    // 1. Update Pet Record
                    $petUpdate = ['last_date' => now()];
                    if ($isVaccine) {
                        $petUpdate['vaccine_type'] = $finalName;
                        $petUpdate['next_date'] = now()->addDays($interval);
                    }
                    if ($isSurgical) {
                        $petUpdate['is_neutered'] = true;
                    }
                    $pet->update($petUpdate);

                    // 2. Create History Record (Only if not already created)
                    // This prevents the "Double Entry" issue
                    $history = Vaccination::create([
                        'pet_id' => $pet->id,
                        'staff_id' => auth()->id(),
                        'vaccine_name' => $finalName,
                        'date_administered' => now(),
                        'next_due_date' => ($interval > 0) ? now()->addDays($interval) : null,
                        'batch_no' => $batchNo,
                        'status' => $isVaccine ? 'Up to Date' : 'Completed'
                    ]);

                    $this->sendTelegramNotification($history, 'treatment_completed');

                    // 3. Sync appointment record
                    $appointment->batch_no = $batchNo;
                    $appointment->next_due_date = ($interval > 0) ? now()->addDays($interval) : null;
                }
            }

            $appointment->save();
            return back()->with('success', "Patient treatment is now Complete");
        } else {
            // Handle simple status updates
            $appointment->status = $newStatus;
            $appointment->save();
            $this->sendTelegramNotification($appointment, 'appointment_status_updated');
            return back()->with('success', "Patient is now " . ucfirst($newStatus));
        }
    }
    public function storeAppointment(Request $request)
    {
        // 1. Validation Logic
        $rules = [
            'owner_status'  => 'required|in:existing,new',
            'service_type'  => 'required',
            'schedule_date' => 'required|date|after_or_equal:today',
            'schedule_time' => 'required', // Ensure time is captured
        ];

        if ($request->owner_status === 'new') {
            $rules['pet_name']   = 'required|string|max:255';
            $rules['species']    = 'required';
            $rules['gender']     = 'required';
            $rules['breed']      = 'required';
            $rules['first_name'] = 'required|string|max:255';
            $rules['last_name']  = 'required|string|max:255';
            $rules['phone']      = 'required|string';
            $rules['email']      = 'nullable|email|unique:users,email';
        } else {
            $rules['user_id'] = 'required|exists:users,id';
            $rules['pet_ids'] = 'required|array|min:1'; // Array of checkboxes
        }

        $request->validate($rules);

        // --- STEP 2: Handle Owner Logic ---
        $userId = null;
        $ownerName = 'Guest';
        $phone = $request->phone;

        if ($request->owner_status === 'existing') {
            $user = User::findOrFail($request->user_id);
            $userId = $user->id;
            $ownerName = $user->name;
            $phone = $user->phone;
        } else {
            $fullName = trim("{$request->first_name} " . ($request->middle_initial ? "{$request->middle_initial}. " : "") . $request->last_name);

            if ($request->has('create_online_account') && $request->email) {
                $plainPassword = 'PawCare2026';
                $user = User::create([
                    'name'      => $fullName,
                    'email'     => $request->email,
                    'phone'     => $request->phone,
                    'role'      => 'owner',
                    'gender'    => $request->owner_gender,
                    'house_no'  => $request->house_no,
                    'street'    => $request->street,
                    'barangay'  => $request->barangay,
                    'city'      => $request->city ?? 'Meycauayan City',
                    'province'  => $request->province ?? 'Bulacan',
                    'password'  => Hash::make($plainPassword),
                ]);
                $userId = $user->id;
                $ownerName = $user->name;

                // Trigger the Welcome Email
                Mail::to($user->email)->send(new WelcomeEmail($user, $plainPassword));

                // Telegram: Account Created
                try { $this->sendTelegramNotification($user, 'account_created'); } catch (\Exception $e) {}
            } else {
                $userId = null;
                $ownerName = $fullName;
            }
        }

        // --- STEP 3: Prepare Pet List ---
        $petsToProcess = [];
        $finalBreed = ($request->breed === 'Other') ? $request->other_breed : $request->breed;

        if ($request->owner_status === 'existing') {
            // Fetch the pets selected via checkboxes
            $petsToProcess = Pet::whereIn('id', $request->pet_ids)->get();
        } else {
            // Create new pet for new owner
            $petCount = Pet::withTrashed()->count() + 1;
            $newPet = Pet::create([
                'user_id'      => $userId,
                'pet_id'       => 'WALK-' . strtoupper(substr(uniqid(), -3)) . '-' . str_pad($petCount, 3, '0', STR_PAD_LEFT),
                'name'         => $request->pet_name,
                'species'      => $request->species,
                'gender'       => $request->gender,
                'birthday'     => $request->birthday ?? now(),
                'breed'        => $finalBreed,
                'owner'        => $ownerName,
                'owner_phone'  => $phone,
                'owner_gender' => $request->owner_gender,
                'status'       => 'ACTIVE',
                'house_no'     => $request->house_no,
                'street'       => $request->street,
                'barangay'     => $request->barangay,
                'city'         => $request->city ?? 'Meycauayan City',
                'province'     => $request->province ?? 'Bulacan',
            ]);
            $petsToProcess[] = $newPet;

            // Telegram: Pet Registered
            try { $this->sendTelegramNotification($newPet, 'pet_registered'); } catch (\Exception $e) {}
        }

        // --- STEP 4: Loop and Create Appointments ---
        $createdCount = 0;
        $errors = [];
        $requestedDate = \Carbon\Carbon::parse($request->schedule_date);

        foreach ($petsToProcess as $pet) {
            // 1. General Rule: 15-day gap check
            $anyLastVax = $pet->vaccinations()->latest('date_administered')->first();
            if ($anyLastVax) {
                $lastAnyDate = \Carbon\Carbon::parse($anyLastVax->date_administered);
                $gap = $lastAnyDate->diffInDays($requestedDate, false);
                if ($gap < 15 && $gap >= 0) {
                    $errors[] = "{$pet->name}: Must wait 15 days from last vaccine.";
                    continue;
                }
            }

            // 2. Specific Medical Eligibility Check
            $medicalError = $this->checkServiceEligibility($pet->id, $request->service_type, $request->schedule_date);
            if ($medicalError) {
                $errors[] = "{$pet->name}: {$medicalError}";
                continue;
            }

            // 3. Create Appointment
            $appointment = Appointment::create([
                'user_id'          => $userId,
                'pet_id'           => $pet->id,
                'pet_name'         => $pet->name,
                'gender'           => $pet->gender,
                'species'          => $pet->species,
                'appointment_date' => $request->schedule_date,
                'appointment_time' => $request->schedule_time,
                'service_type'     => $request->service_type,
                'status'           => 'approved',
            ]);

            // Telegram: New Appointment
            try { $this->sendTelegramNotification($appointment, 'appointment_new'); } catch (\Exception $e) {}

            $createdCount++;
        }

        // --- STEP 5: Response Handling ---
        if ($createdCount === 0) {
            $msg = count($errors) > 0 ? implode(' ', $errors) : 'No appointments could be created.';
            return back()->with('warning', $msg)->withInput();
        }

        $successMsg = "Successfully created $createdCount walk-in appointment(s) for $ownerName.";
        if (count($errors) > 0) {
            $successMsg .= " Note: " . implode(' ', $errors);
        }

        return back()->with('success', $successMsg);
    }

    private function checkServiceEligibility($petId, $serviceType, $requestedDate)
    {
        $requestedDate = \Carbon\Carbon::parse($requestedDate);
        $serviceTypeLower = strtolower($serviceType);

        // --- 1. CHECK PENDING APPOINTMENTS ---
        // Prevent booking the same service if they already have an upcoming appointment
        $existingAppointment = Appointment::where('pet_id', $petId)
            ->where('service_type', $serviceType)
            ->whereIn('status', ['pending', 'approved'])
            ->where('appointment_date', '>=', now()->toDateString())
            ->first();

        if ($existingAppointment) {
            $date = \Carbon\Carbon::parse($existingAppointment->appointment_date)->format('M d, Y');
            return "This pet already has an upcoming {$serviceType} appointment scheduled for {$date}.";
        }

        // --- 2. CHECK VACCINATION HISTORY ---
        $lastVax = Vaccination::where('pet_id', $petId)
            ->where('vaccine_name', 'LIKE', '%' . $serviceType . '%')
            ->latest('date_administered')
            ->first();

        if (!$lastVax) return null; // No history, eligible to proceed

        $lastDate = \Carbon\Carbon::parse($lastVax->date_administered);
        $daysSince = $lastDate->diffInDays($requestedDate, false);

        // --- 3. APPLY MEDICAL INTERVALS ---

        // Anti-Rabies: Yearly (365 days)
        if (str_contains($serviceTypeLower, 'rabies') || str_contains($serviceTypeLower, 'rabisin')) {
            if ($daysSince < 365) {
                $nextDue = $lastDate->addYear()->format('M d, Y');
                return "Anti-Rabies is administered annually. The next dose is not due until {$nextDue}.";
            }
        }

        // 5-in-1 / 4-Way / DHPP: 14 to 28 days
        if (str_contains($serviceTypeLower, '5-in-1') || str_contains($serviceTypeLower, '4-way') || str_contains($serviceTypeLower, 'dhpp')) {
            if ($daysSince < 14) {
                $nextDue = $lastDate->addDays(14)->format('M d, Y');
                return "Boosters for {$serviceType} require a 14-day gap. Earliest availability: {$nextDue}.";
            }
        }

        // Deworming: Every 3 Months (90 days)
        if (str_contains($serviceTypeLower, 'deworming')) {
            if ($daysSince < 90) {
                $nextDue = $lastDate->addMonths(3)->format('M d, Y');
                return "Deworming was recently performed. Next scheduled dose: {$nextDue}.";
            }
        }

        return null; // Passed all checks
    }
    public function petRecords(Request $request)
    {
        $search = $request->query('search');
        $view = $request->input('view', 'active');
        $statusFilter = $request->input('status');

        // 1. CLEAN THE SEARCH TERM BEFORE THE QUERY
        if ($search && str_contains($search, '/verify-pet/')) {
            $search = last(explode('/verify-pet/', $search));
        }

        $query = Pet::with(['user', 'vaccinations']);

        // --- Specific ID Search ---
        $isSpecificId = $search && (
            str_starts_with(strtoupper($search), 'PC-') ||
            str_starts_with(strtoupper($search), 'WALK-') ||
            is_numeric($search)
        );

        if ($isSpecificId) {
            $query->withTrashed()->where(function ($q) use ($search) {
                $q->where('id', $search)->orWhere('pet_id', $search);
            });
        } else {
            // --- View Logic (Active/Archived) ---
            if ($view === 'archived') {
                $query->withTrashed()->where(function ($q) {
                    $q->whereIn('status', ['DECEASED', 'INACTIVE'])->orWhereNotNull('deleted_at');
                });
            } else {
                $query->notDeceased();
            }

            // --- Status Filtering Logic ---
            if ($statusFilter) {
                switch ($statusFilter) {
                    case 'unvaccinated':
                        // 1. No records at all
                        $query->doesntHave('vaccinations');
                        break;

                    case 'vaccinated':
                        // 2. Has records AND is NOT overdue AND is NOT due within 14 days
                        $query->has('vaccinations')
                            ->whereDoesntHave('vaccinations', function($q) {
                                // Exclude if expired OR expiring within 14 days
                                $q->where('next_due_date', '<', now()->addDays(14));
                            });
                        break;

                    case 'due_soon':
                        // 3. Has records where the NEXT due date is between today and 14 days from now
                        $query->whereHas('vaccinations', function($q) {
                            $q->whereBetween('next_due_date', [now(), now()->addDays(14)]);
                        });
                        break;

                    case 'overdue':
                        // 4. Has records where the date has already passed
                        $query->whereHas('vaccinations', function($q) {
                            $q->where('next_due_date', '<', now());
                        });
                        break;
                }
            }

            // --- Name/Owner Search ---
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('owner', 'like', "%{$search}%");
                });
            }
        }

        $pets = $query->latest()
            ->paginate(10)
            ->appends(['search' => $search, 'view' => $view, 'status' => $statusFilter]);

        $owners = User::where('role', 'owner')
        ->orderBy('name')
        ->get();

        return view('staff.pet-records', compact('pets', 'search', 'view', 'statusFilter', 'owners'));
    }
    public function vaccinationStatus(Request $request)
    {
        // 1. Start query with relationships
        $query = Pet::notDeceased()->with(['user', 'latestVaccination', 'vaccinations', 'appointments']);

        // 2. Filter logic: Show pets with RECENT activity or pending approved appointments
        $query->whereHas('appointments', function ($q) {
            $q->whereDate('appointment_date', today())
            // Added 'completed' and 'done' to the list so they stay on the tracker for the day
            ->whereIn('status', ['approved', 'checked-in', 'completed', 'done'])
            ->whereIn('service_type', [
                    'Anti-Rabies', '5-in-1', '5in1', '4-in-1 (Cat)',
                    '4in1', 'Deworming', 'Check-up', 'Kapon', 'Vaccination'
            ]);
        });

        // Search Filter
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('owner', 'like', "%{$request->search}%")
                    ->orWhere('pet_id', 'like', "%{$request->search}%");
            });
        }

        // Filter for pets vaccinated today
        if ($request->has('today')) {
        $query->whereHas('vaccinations', function($q) {
            $q->whereDate('date_administered', today());
        });
        }

        $pets = $query->latest()->paginate(10)->appends($request->query());
        return view('staff.vaccination-status', compact('pets'));
    }

    public function vaccinationHistory(Request $request)
    {
        $query = Vaccination::whereHas('pet', function ($q) {
            $q->notDeceased();
        })->with(['pet', 'staff']);

        // --- Date Range Filter ---
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date_administered', [$request->start_date, $request->end_date]);
        }

        // --- Quick Period Filters (Updated to match Blade) ---
        $period = $request->get('period');
        if ($period == 'today') {
            $query->whereDate('date_administered', today());
        } elseif ($period == 'weekly') {
            $query->whereBetween('date_administered', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period == 'monthly') {
            $query->whereMonth('date_administered', now()->month)
                ->whereYear('date_administered', now()->year);
        }

        // --- Dropdown Filters ---
        if ($request->filled('pet_id')) {
            $query->where('pet_id', $request->pet_id);
        }

        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->filled('vaccine_name')) {
            $query->where('vaccine_name', $request->vaccine_name);
        }

        // --- Pagination (15 records per page) ---
        $history = $query->latest('date_administered')
                        ->paginate(10)
                        ->appends($request->all()); // This keeps your filters when you click next page

        $staffList = User::where('role', 'staff')->get();
        $vaccineList = Vaccination::select('vaccine_name as name')->distinct()->get();

        return view('staff.vaccination-history', compact('history', 'staffList', 'vaccineList'));
    }

    public function profile()
    {
        $staff = Auth::user();
        return view('staff.profile', compact('staff'));
    }

    private function checkVaccineInterval($petId, $newVaccine, $dateAdministered)
    {
        $lastVax = Vaccination::where('pet_id', $petId)
            ->latest('date_administered')
            ->first();

        if (!$lastVax) return null;

        $lastDate = \Carbon\Carbon::parse($lastVax->date_administered);
        $newDate = \Carbon\Carbon::parse($dateAdministered);
        $daysSinceLast = $lastDate->diffInDays($newDate);

        $newVaccineLower = strtolower(trim($newVaccine));
        $lastVaccineLower = strtolower(trim($lastVax->vaccine_name));

        // Rule 1: Same Vaccine type - must wait for Next Due Date
        if ($newVaccineLower === $lastVaccineLower) {
            $dueDate = \Carbon\Carbon::parse($lastVax->next_due_date);
            if ($newDate->lt($dueDate)) {
                return "This pet is not yet due for another {$lastVax->vaccine_name}. Next due date is " . $dueDate->format('M d, Y');
            }
        }

        // Rule 2: Different Vaccine - General 15-day cooling period
        if ($daysSinceLast < 15) {
            return "Please wait at least 15 days between different vaccine types. Last shot was " . $daysSinceLast . " days ago.";
        }

        return null;
    }

    public function updateVaccination(Request $request, $id)
    {
        $request->validate([
            'vaccine_name' => 'required',
            'date_administered' => 'required|date',
            'next_due_date' => 'required|date',
        ]);

        $intervalError = $this->checkVaccineInterval($id, $request->vaccine_name, $request->date_administered);
        if ($intervalError) {
            return back()->withErrors(['vaccine_name' => $intervalError])->withInput();
        }

        $actualBatchNo = 'MANUAL-' . date('Ymd');

        // 1. Create Vaccination Record
        $vaccination = Vaccination::create([
            'pet_id' => $id,
            'staff_id' => auth()->id(),
            'vaccine_name' => $request->vaccine_name,
            'date_administered' => $request->date_administered,
            'next_due_date' => $request->next_due_date,
            'batch_no' => $actualBatchNo,
        ]);

        $this->sendTelegramNotification($vaccination, 'vaccination_updated');

        // 2. Update Pet Medical Record
        $pet = Pet::findOrFail($id);
        $pet->update([
            'vaccine_type' => $request->vaccine_name,
            'last_date' => $request->date_administered,
            'next_date' => $request->next_due_date,
        ]);

        // 3. Update the Appointment status
        if ($request->appointment_id) {
            $appointment = Appointment::find($request->appointment_id);
            if ($appointment) {
                $appointment->update([
                    'status' => 'completed', // Status changes to completed here
                    'administered_by' => auth()->user()->name,
                    'batch_no' => $actualBatchNo,
                    'vaccine_name' => $request->vaccine_name,
                    'next_due_date' => $request->next_due_date,
                ]);
            }
        }

        return back()->with('success', "Vaccination logged and status updated!");
    }

    public function requestDigitalCard(Request $request, $id)
    {
        // Validates and adds a new record to the Vaccination history
        $request->validate([
            'vaccine_name' => 'required|string',
            'date_administered' => 'required|date',
        ]);

        Vaccination::create([
            'pet_id' => $id,
            'vaccine_name' => $request->vaccine_name,
            'date_administered' => $request->date_administered,
            'next_due_date' => $request->next_due_date,
            'remarks' => $request->remarks,
        ]);

        return back()->with('success', 'Vaccination record added!');
    }
    public function ownerProfile(Request $request, $id)
    {
        // 1. If the request explicitly says 'walkin', skip User table and go to Pet table
        if ($request->query('type') === 'walkin') {
            $pet = Pet::findOrFail($id);
            return $this->buildWalkinObject($pet);
        }

        // 2. Try to find an actual Registered User
        $owner = User::with('pets')->find($id);

        // 3. If User exists, show their profile
        if ($owner) {
            return view('staff.pet-owners', compact('owner'));
        }

        // 4. Fallback: If no User was found with that ID, check if it's a Pet ID
        $pet = Pet::findOrFail($id);
        return $this->buildWalkinObject($pet);
    }

    private function buildWalkinObject($pet)
    {
        $owner = (object)[
            'id' => null,
            'pet_id' => $pet->id,
            'name' => $pet->owner,
            'phone' => $pet->owner_phone,
            'email' => null,
            'password' => null,
            'house_no' => $pet->house_no,
            'street' => $pet->street,
            'barangay' => $pet->barangay,
            'city' => $pet->city ?? 'Meycauayan City',
            'province' => $pet->province ?? 'Bulacan',
            'pets' => collect([$pet])
        ];

        return view('staff.pet-owners', compact('owner'));
    }

    public function createAccount(Request $request, $id)
    {
        // 1. Determine if we are upgrading a Walk-in Pet record or an existing User record
        if ($request->input('is_walkin') == '1') {
            // Find the pet to get the owner details
            $pet = Pet::findOrFail($id);

            // Validate the email (since walk-ins usually don't have one)
            $request->validate([
                'email' => 'required|email|unique:users,email'
            ]);

            $plainPassword = 'PawCare2026';

            // Create the new User record
            $owner = User::create([
                'name' => $pet->owner,
                'email' => $request->email,
                'phone' => $pet->owner_phone ?? 'N/A',
                'role' => 'owner',
                'password' => Hash::make($plainPassword),
                // Copy address from pet record if you have those columns
                'house_no' => $pet->house_no,
                'street' => $pet->street,
                'barangay' => $pet->barangay,
                'city' => 'Meycauayan',
                'province' => 'Bulacan',
            ]);

            // Link THIS pet (and any others with the same owner name/phone) to the new user
            Pet::where('owner', $pet->owner)
                ->whereNull('user_id')
                ->update(['user_id' => $owner->id]);

        } else {
            // Standard flow for existing User record without a password
            $owner = User::findOrFail($id);

            if (!$owner->email) {
                $request->validate(['email' => 'required|email|unique:users,email']);
                $owner->email = $request->email;
            }

            $plainPassword = 'PawCare2026';
            $owner->password = Hash::make($plainPassword);
            $owner->save();

            // --- NEW: Send Telegram Notification to Clinic Owner ---
            $this->sendTelegramNotification($owner, 'account_created');
        }

        // 2. Send the Welcome Email
        Mail::send('emails.welcome', ['user' => $owner, 'password' => $plainPassword], function($message) use ($owner) {
            $message->to($owner->email)->subject('Welcome to PawCare! 🐾');
        });

        return redirect()->route('staff.pet-owners', $owner->id)
            ->with('success', 'Online account activated! Credentials sent to ' . $owner->email);
    }

    public function storeOwner(Request $request)
    {
        // 1. Validate under the "One-Account Policy"
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:2',
            'email' => 'required|email|unique:users',
            'phone' => 'required|numeric|digits:11|unique:users',
            'house_no' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'gender' => 'required|string|in:Male,Female',
            'profile_image' => 'nullable|image|max:2048',
            'profile_image_base64' => 'nullable|string',
        ], [
            'email.unique' => 'An account with this email address already exists.',
            'phone.unique' => 'An account with this mobile number already exists.',
        ]);

        $imagePath = null;
        if ($request->filled('profile_image_base64')) {
            $imageData = $request->input('profile_image_base64');
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
            $imageName = 'profiles/' . uniqid() . '.png';
            Storage::disk('public')->put($imageName, base64_decode($imageData));
            $imagePath = $imageName;
        } elseif ($request->hasFile('profile_image')) {
            $imagePath = $request->file('profile_image')->store('profiles', 'public');
        }

        // 2. Generate a secure random password
        $rawPassword = \Illuminate\Support\Str::random(8);

        // 3. Create the Owner User
        $fullName = trim($request->first_name . ' ' . ($request->middle_initial ? $request->middle_initial . ' ' : '') . $request->last_name);

        $user = User::create([
            'name' => $fullName,
            'email' => $request->email,
            'password' => Hash::make($rawPassword),
            'phone' => $request->phone,
            'gender' => $request->gender,
            'role' => 'owner',
            'profile_image' => $imagePath,

            // Granular Address Fields
            'house_no' => $request->house_no,
            'street' => $request->street,
            'barangay' => $request->barangay,
            'city' => $request->city,
            'province' => $request->province,

            'address' => "{$request->house_no} {$request->street}, {$request->barangay}, {$request->city}, {$request->province}",
        ]);

        // 4. Record Activity
        \App\Models\ActivityLog::record(
            'CREATE_OWNER',
            'Staff successfully created a new owner account for: ' . $user->name
        );

        // 5. Send Automated Welcome Email
        try {
            Mail::to($user->email)->send(new WelcomeEmail($user, $rawPassword));

            // --- Telegram Notification ---
            $this->sendTelegramNotification($user, 'account_created');

        } catch (\Throwable $e) {
            return back()->with('error', 'Owner registered, but email failed! Password is: ' . $rawPassword);
        }

        return back()->with('success', 'New owner account successfully registered!');
    }

    public function reschedule(Request $request, $id)
    {
        $request->validate([
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
        ]);

        $appointment = Appointment::findOrFail($id);
        $requestedTime = $request->appointment_time;

        // Check availability for Kapon (1 hour block)
        if (strtolower($appointment->service_type) === 'kapon') {
            $nextSlot = \Carbon\Carbon::parse($requestedTime)->addMinutes(30)->format('H:i');

            $isConflict = Appointment::whereDate('appointment_date', $request->appointment_date)
                ->where('id', '!=', $id) // Exclude current appointment
                ->whereIn('status', ['approved', 'checked-in', 'Done', 'completed', 'rescheduled'])
                ->where(function($q) use ($requestedTime, $nextSlot) {
                    $q->where('appointment_time', $requestedTime)
                    ->orWhere('appointment_time', $nextSlot);
                })->exists();

            if ($isConflict) {
                return back()->with('error', 'This time slot requires a 1-hour window for Kapon, but the next slot is already booked.');
            }
        }

        $appointment->update([
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $requestedTime,
            'status' => 'rescheduled',
        ]);

        return back()->with('success', "Appointment for {$appointment->pet_name} rescheduled to " . \Carbon\Carbon::parse($request->appointment_date)->format('M d, Y') . " at " . \Carbon\Carbon::parse($requestedTime)->format('g:i A'));
    }
    public function getBookedSlots(Request $request)
    {
        $date = $request->query('date');

        // 1. Fetch all active appointments for the chosen date
        $appointments = Appointment::whereDate('appointment_date', $date)
            ->whereIn('status', ['approved', 'checked-in', 'Done', 'completed', 'rescheduled', 'late'])
            ->get();

        $bookedSlots = [];

        foreach ($appointments as $apt) {
            // Format the booked time (e.g., "08:30")
            $startTime = \Carbon\Carbon::parse($apt->appointment_time)->format('H:i');
            $bookedSlots[] = $startTime;

            // 2. Logic for Kapon: It blocks the current AND the next 30-min slot
            if (strtolower($apt->service_type) === 'kapon') {
                $nextSlot = \Carbon\Carbon::parse($apt->appointment_time)->addMinutes(30)->format('H:i');
                $bookedSlots[] = $nextSlot;
            }
        }

        // Return unique slots so JavaScript can disable them in the dropdown
        return response()->json(array_values(array_unique($bookedSlots)));
    }
    public function generateReport(Request $request)
    {
        $reportCategory = $request->get('type');
        $filter = $request->get('filter', 'today');
        $type = ($reportCategory === 'vaccination_history') ? 'vaccination' : $filter;
        $today = now()->toDateString();
        $summaryData = [];

        // --- CASE A: VACCINATION HISTORY REPORT ---
        if ($reportCategory === 'vaccination_history') {
            $query = Vaccination::with(['pet', 'staff']);
            $reportTitle = "VACCINATION HISTORY REPORT";
            $viewPath = 'staff.reports.vaccination_history_report';

            if ($request->filled('period')) {
                $period = $request->get('period');
                if ($period == 'today') $query->whereDate('date_administered', today());
                elseif ($period == 'weekly') $query->whereBetween('date_administered', [now()->startOfWeek(), now()->endOfWeek()]);
                elseif ($period == 'monthly') $query->whereMonth('date_administered', now()->month)->whereYear('date_administered', now()->year);
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('date_administered', [$request->start_date, $request->end_date]);
            }

            if ($request->filled('vaccine_name')) $query->where('vaccine_name', $request->vaccine_name);
            if ($request->filled('staff_id')) $query->where('staff_id', $request->staff_id);

            $data = $query->latest('date_administered')->get();

        } else {
            // --- CASE B: DAILY ACCOMPLISHMENT / APPOINTMENT REPORTS ---
            $viewPath = 'staff.reports.staff_appointment';

            // 1. Fetch ALL data for today for accurate Overview counts
            $allToday = Appointment::whereDate('appointment_date', $today)->get();

            // 2. Updated Summary Data with robust string matching
            $summaryData = [
                'date'        => now()->format('M d, Y'),
                'total'       => $allToday->count(),
                'anti_rabies' => $allToday->filter(fn($a) => stripos($a->service_type, 'rabies') !== false)->count(),
                'five_in_one' => $allToday->filter(fn($a) => str_contains($a->service_type, '5-in-1'))->count(),
                'four_in_one' => $allToday->filter(fn($a) => str_contains($a->service_type, '4-in-1'))->count(),
                'deworming'   => $allToday->filter(fn($a) => stripos($a->service_type, 'deworming') !== false)->count(),
                'completed'   => $allToday->whereIn('status', ['done', 'completed'])->count(),
                'missed'      => $allToday->where('status', 'missed')->count(),
            ];

            // 3. Filtering what shows in the main table based on dropdown
            $query = Appointment::with(['pet', 'user'])->whereDate('appointment_date', $today);

            if ($filter === 'completed') {
                $query->whereIn('status', ['done', 'completed']);
                $reportTitle = "COMPLETED APPOINTMENTS REPORT";
            } elseif ($filter === 'missed') {
                $query->where('status', 'missed');
                $reportTitle = "MISSED APPOINTMENTS REPORT";
            } else {
                $reportTitle = "DAILY ACCOMPLISHMENT SUMMARY";
            }

            $data = $query->orderBy('appointment_time', 'asc')->get();
        }

        // PDF or View Return
        if ($request->has('pdf')) {
            $pdf = Pdf::loadView($viewPath, compact('data', 'reportTitle', 'type', 'summaryData', 'filter'))
                        ->setPaper('a4', 'portrait');
            return $pdf->download("PawCare_Report_" . now()->format('Y-m-d') . ".pdf");
        }

        return view($viewPath, compact('data', 'reportTitle', 'type', 'filter', 'summaryData'));
    }
    public function getPetsByOwner($userId)
{
   $pets = Pet::where('user_id', $userId)
                ->notDeceased()
                ->select('id', 'name', 'species', 'gender', 'breed', 'birthday')
                ->get();

    return response()->json($pets);
}

public function storePet(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'name' => 'required|string|max:255',
        'gender' => 'required',
        'species' => 'required|string|in:Dog,Cat',
        'breed' => 'nullable|string|max:255',
        'other_breed' => 'nullable|string|max:255',
        'birthday' => 'required|date|before_or_equal:today',
        'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    // Handle "Other" logic and Capitalization
    $finalBreed = $request->breed;
    if ($request->breed === 'Other' && $request->filled('other_breed')) {
        $finalBreed = Str::title(trim($request->other_breed));
    }

    $imagePath = null;
    // 1. Check for Base64 first (Camera Capture)
    if ($request->filled('image_base64')) {
        $imageData = $request->input('image_base64');
        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
        $imageData = base64_decode($imageData);

        $fileName = 'pets/' . uniqid() . '.png';
        Storage::disk('public')->put($fileName, $imageData);
        $imagePath = $fileName;
    }
    // 2. Fallback to standard file upload
    else if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('pets', 'public');
    }

    $year = date('Y');
    $count = Pet::withTrashed()->count() + 1;
    $unique_id = 'PC-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

    $pet = Pet::create([
        'user_id' => $request->user_id,
        'pet_id' => $unique_id,
        'name' => $request->name,
        'species' => $request->species,
        'breed' => $finalBreed ?? 'Unknown',
        'birthday' => $request->birthday,
        'gender' => $request->gender,
        'owner' => User::find($request->user_id)->name ?? 'Unknown',
        'image_url' => $imagePath,
        'status' => 'Verified', // Still verified because it's clinic staff
    ]);

    // Updated Log message to reflect "Staff" instead of "Admin"
    ActivityLog::record(
        'CREATE_PET',
        "Staff successfully registered a new pet ({$pet->name}) for owner ID: {$pet->user_id}"
    );

    // Telegram Notification
    try {
        $this->sendTelegramNotification($pet, 'pet_registered');
    } catch (\Throwable $e) {
        Log::error('Failed to send Telegram pet registration alert (Staff): ' . $e->getMessage());
    }

    return back()->with('success', 'Pet successfully registered! Pet ID: ' . $unique_id);
}
}
