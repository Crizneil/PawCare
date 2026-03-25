<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\Appointment;
use App\Models\User;
use App\Models\UserRequest;
use App\Models\Vaccination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $overdueAppointments = Appointment::whereDate('appointment_date', today())
            ->whereIn('status', ['approved', 'pending', 'late'])
            ->get();

        foreach ($overdueAppointments as $apt) {
            $scheduledTime = \Carbon\Carbon::parse($apt->appointment_date . ' ' . $apt->appointment_time);

            // If current time is 15 mins past schedule (diff < -15)
            if (now()->diffInMinutes($scheduledTime, false) < -15) {
                DB::table('appointments')->where('id', $apt->id)->update(['status' => 'missed']);
            }
        }
        $query = Appointment::with(['user', 'pet']);

        $appointments = match ($view) {
            'upcoming' => $query->where('appointment_date', '>', today())
                                ->whereIn('status', ['approved', 'rescheduled']),

            'completed' => $query->whereIn('status', ['done', 'completed']),

            'missed' => $query->whereIn('status', ['missed']), // Explicitly missed

            // Today's view now includes the active workflow statuses
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
            'owner_status' => 'required|in:existing,new',
            'pet_name' => 'required|string|max:255',
            'species' => 'required',
            'gender' => 'required',
            'breed' => 'required',
            'service_type' => 'required',
            'schedule_date' => 'required|date|after_or_equal:today',
            'birthday' => 'nullable|date|before_or_equal:today',
        ];

        if ($request->owner_status === 'new') {
            $rules['first_name'] = 'required|string|max:255';
            $rules['last_name'] = 'required|string|max:255';
            $rules['phone'] = 'required|string';
            $rules['email'] = 'nullable|email|unique:users,email';
        } else {
            $rules['user_id'] = 'required|exists:users,id';
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
                    'name' => $fullName,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'role' => 'owner',
                    'gender' => $request->owner_gender,
                    'house_no' => $request->house_no,
                    'street' => $request->street,
                    'barangay' => $request->barangay,
                    'city' => $request->city ?? 'Meycauayan City',
                    'province' => $request->province ?? 'Bulacan',
                    'password' => Hash::make($plainPassword),
                ]);
                $userId = $user->id;
                $ownerName = $user->name;

                // --- ADDED THIS: Trigger the Welcome Email ---
                // Use the WelcomeEmail class you imported at the top of the file
                Mail::to($user->email)->send(new WelcomeEmail($user, $plainPassword));

            } else {
                $userId = null;
                $ownerName = $fullName;
            }
        }

        // 3. Handle Breed Logic
        $finalBreed = ($request->breed === 'Other') ? $request->other_breed : $request->breed;

        // 4. Handle Pet Logic (Find Existing or Create New)
        $pet = null;
        if ($userId) {
            // If existing owner, check if we received a specific Pet ID or just a Name
            if (is_numeric($request->pet_name)) {
                $pet = Pet::where('user_id', $userId)->where('id', $request->pet_name)->first();
            }

            // If not found by ID, try searching by name for this owner
            if (!$pet) {
                $pet = Pet::where('user_id', $userId)
                          ->where('name', $request->pet_name)
                          ->first();
            }
        }

        // If still no pet, create a new record
        if (!$pet) {
            $petCount = Pet::withTrashed()->count() + 1;
            $pet = Pet::create([
                'user_id' => $userId,
                'pet_id' => 'WALK-' . strtoupper(substr(uniqid(), -3)) . '-' . str_pad($petCount, 3, '0', STR_PAD_LEFT),
                'name' => $request->pet_name,
                'species' => $request->species,
                'gender' => $request->gender,
                'birthday' => $request->birthday ?? now(),
                'breed' => $finalBreed,
                'owner' => $ownerName,
                'owner_phone' => $phone,
                'owner_gender' => $request->owner_gender,
                'status' => 'ACTIVE',
                'house_no' => $request->house_no,
                'street' => $request->street,
                'barangay' => $request->barangay,
                'city' => $request->city ?? 'Meycauayan City',
                'province' => $request->province ?? 'Bulacan',
            ]);
        }

        // --- NEW: Centralized Service & Vaccination Eligibility Validation ---
        \Log::info('--- WALK-IN DEBUG ---', [
            'pet_id' => $pet->id,
            'pet_name_req' => $request->pet_name,
            'is_numeric_pet_name' => is_numeric($request->pet_name),
            'service_type' => $request->service_type,
            'schedule_date' => $request->schedule_date
        ]);
        $error = $this->checkServiceEligibility($pet->id, $request->service_type, $request->schedule_date);
        \Log::info('checkServiceEligibility Error Output', ['error' => $error]);

        if ($error) {
            return back()->withErrors(['service_type' => $error])->withInput();
        }
        // --- End of Validation ---

        // 5. Create the Appointment
        $appointment = Appointment::create([
            'user_id' => $userId,
            'pet_id' => $pet->id,
            'pet_name' => $pet->name,
            'gender' => $request->gender,
            'species' => $pet->species,
            'appointment_date' => $request->schedule_date ?? now()->toDateString(),
            'appointment_time' => $request->schedule_time,
            'service_type' => $request->service_type,
            'status' => 'approved',
        ]);

        // --- NEW: Send Telegram Notification to Clinic Owner ---
        try {
            $this->sendTelegramNotification($appointment, 'appointment_new');

            if ($userId) {
                // Also notify about the new account if it was created
                $this->sendTelegramNotification(User::find($userId), 'account_created');
            }

            // Also notify about the pet registration
            $this->sendTelegramNotification($pet, 'pet_registered');

        } catch (\Throwable $e) {
            \Log::error('Failed to send Telegram walk-in alerts: ' . $e->getMessage());
        }

        return back()->with('success', 'Walk-in appointment created and welcome email sent to ' . $ownerName);
    }
    public function petRecords(Request $request)
    {
        $search = $request->query('search');
        $view = $request->input('view', 'active');

        // 1. CLEAN THE SEARCH TERM BEFORE THE QUERY
        if ($search && str_contains($search, '/verify-pet/')) {
            $search = last(explode('/verify-pet/', $search));
        }

        $query = Pet::with(['user', 'vaccinations']);

        // Check if $search is exactly a pet ID or internal ID
        // (Assuming if it matches 'PC-' or is numeric, it's likely a direct ID search)
        $isSpecificId = $search && (
            str_starts_with(strtoupper($search), 'PC-') ||
            str_starts_with(strtoupper($search), 'WALK-') ||
            is_numeric($search)
        );

        if ($isSpecificId) {
            $query->withTrashed()->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('pet_id', $search);
            });
        } else {
            if ($view === 'archived') {
                $query->withTrashed()->where(function ($q) {
                    $q->whereIn('status', ['DECEASED', 'INACTIVE'])
                        ->orWhereNotNull('deleted_at');
                });
            } else {
                $query->notDeceased();
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('owner', 'like', "%{$search}%");
                });
            }
        }

        $pets = $query->latest()
            ->paginate(10)
            ->appends(['search' => $search, 'view' => $view]);

        return view('staff.pet-records', compact('pets', 'search', 'view'));
    }
    public function vaccinationStatus(Request $request)
    {
        // 1. Start query with relationships
        $query = Pet::notDeceased()->with(['user', 'latestVaccination', 'appointments']);

        // 2. Filter logic: Show pets with RECENT activity or pending approved appointments
        $query->whereHas('appointments', function ($q) {
            $q->whereIn('status', ['approved', 'checked-in', 'Done', 'completed', 'rescheduled', 'late'])
            ->whereIn('service_type', [
                'Anti-Rabies',
                '5-in-1', '5in1',
                '4-in-1 (Cat)', '4in1',
                'Deworming',
                'Check-up',
                'Kapon',
                'Vaccination'
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

        // --- Existing Quick Filters ---
        if ($request->filter == 'today') {
            $query->whereDate('date_administered', today());
        } elseif ($request->filter == 'week') {
            $query->whereBetween('date_administered', [now()->startOfWeek(), now()->endOfWeek()]);
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

        $history = $query->latest('date_administered')->paginate(15)->appends($request->all());

        $staffList = User::where('role', 'staff')->get();
        $vaccineList = Vaccination::select('vaccine_name as name')->distinct()->get();

        return view('staff.vaccination-history', compact('history', 'staffList', 'vaccineList'));
    }

    public function profile()
    {
        $staff = Auth::user();
        return view('staff.profile', compact('staff'));
    }

    public function updateVaccination(Request $request, $id)
    {
        $request->validate([
            'vaccine_name' => 'required',
            'date_administered' => 'required|date',
            'next_due_date' => 'required|date',
        ]);

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

        // --- NEW: Send Telegram Notification to Clinic Owner ---
        $this->sendTelegramNotification($vaccination, 'vaccination_updated');

        $interval = $this->getServiceInterval($request->vaccine_name);

        // 2. Update Pet Medical Record
        $pet = Pet::findOrFail($id);
        $pet->update([
            'vaccine_type' => $request->vaccine_name,
            'last_date' => $request->date_administered,
            'next_date' => $request->next_due_date, // Note: form usually provides this, but we've improved the default logic elsewhere
        ]);

        // 3. Update the Appointment status so the badge changes
        if ($request->appointment_id) {
            $appointment = Appointment::find($request->appointment_id);
            if ($appointment) {
                $appointment->update([
                    'status' => 'Done', // This switches the badge from "Ready" to "Completed"
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
}
