<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\Appointment;
use App\Models\User;
use App\Models\UserRequest;
use App\Models\Vaccination;
use App\Models\VaccineInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;
use Barryvdh\DomPDF\Facade\Pdf;

class StaffController extends Controller
{
    public function dashboard()
    {
        return view('staff.dashboard', [
            'appointmentsToday' => Appointment::whereDate('appointment_date', today())
                ->whereIn('status', ['approved', 'checked-in'])
                ->get(),
            'dueForVaccination' => Pet::notDeceased()->where('status', 'needs_booster')->limit(5)->get(),
            'lowStock' => VaccineInventory::whereColumn('stock', '<=', 'low_stock_threshold')->get(),
            'recentVaccinations' => Vaccination::whereHas('pet', function ($q) {
                $q->notDeceased();
            })->with('pet')->latest()->limit(5)->get(),

            'owners' => User::where('role', 'owner')->orderBy('name')->get()
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
                $apt->update(['status' => 'missed']);
            }
        }
        $query = Appointment::with(['user', 'pet']);

        $appointments = match ($view) {
            'upcoming' => $query->where('appointment_date', '>', today())
                                ->whereIn('status', ['approved', 'rescheduled']),

            'completed' => $query->whereIn('status', ['Done', 'completed']),

            'missed' => $query->whereIn('status', ['missed']), // Explicitly missed

            // Today's view now includes the active workflow statuses
            default => $query->whereDate('appointment_date', today())
                                ->whereIn('status', ['pending', 'approved', 'checked-in', 'late']),
        };

        $paginatedAppointments = $appointments->orderBy('appointment_date')
                             ->orderBy('appointment_time')
                             ->paginate(10)
                             ->appends(['view' => $view]);

        return view('staff.appointments', [
            'appointments' => $paginatedAppointments,
            'view' => $view,
            'owners' => User::where('role', 'owner')->orderBy('name')->get()
        ]);
    }

    public function updateAppointmentStatus(Request $request, $id,)
    {
        $request->validate([
            'status' => 'required|string'
        ]);

        $newStatus = $request->status;
        $appointment = Appointment::findOrFail($id);
        $checkStatus = strtolower($newStatus);

        if (in_array($checkStatus, ['done', 'completed'])) {

            // 1. Prevent double processing
            if ($appointment->status === 'Done' || $appointment->status === 'completed') {
                return back()->with('info', 'This appointment is already processed.');
            }

            $appointment->administered_by = auth()->user()->name;
            $appointment->status = 'completed';

            // 2. Run Medical Services Logic (Vaccination/Inventory)
            $recordName = $appointment->vaccine_name ?? $appointment->service_type;
            $inventory = VaccineInventory::where('name', $recordName)->first();
            $medicalServices = ['Vaccination', 'Deworming', 'Check-up', 'Kapon'];

            if (in_array($appointment->service_type, $medicalServices)) {
                $finalName = $inventory ? $inventory->name : $recordName;
                $batchNo = $inventory ? $inventory->batch_no : 'MANUAL-'.date('Ymd');

                $pet = Pet::find($appointment->pet_id);
                if ($pet) {
                    // Update Pet Medical State
                    $pet->update([
                        'vaccine_type' => $finalName,
                        'last_date' => now(),
                        'next_date' => now()->addYear(),
                    ]);

                    // Create History Record
                    Vaccination::create([
                        'pet_id' => $pet->id,
                        'staff_id' => auth()->id(),
                        'vaccine_name' => $finalName,
                        'date_administered' => now(),
                        'next_due_date' => now()->addYear(),
                        'batch_no' => $batchNo,
                        'status' => 'Up to Date'
                    ]);
                }

                // Deduct from Vaccine Inventory
                if ($inventory && $inventory->stock > 0) {
                    $inventory->decrement('stock', 1);
                }
                $appointment->batch_no = $batchNo;
                $appointment->vaccine_name = $finalName;
                $appointment->next_due_date = now()->addYear();
                $appointment->administered_by = auth()->user()->name;
            }

            $appointment->save();
            return back()->with('success', "Patient treatment is now Complete");

        } else {
            // Handle simple status updates like 'checked-in', 'late', etc.
            $appointment->status = $newStatus;
            if (!$appointment->status) {
                return back()->with('error', 'Invalid status provided.');
            }

            $appointment->save();
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

        // 4. Create Pet Record
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

        // 5. Create the Appointment
        Appointment::create([
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
            ->whereIn('service_type', ['Vaccination', 'Deworming', 'Check-up', 'Kapon']);
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
        $vaccineList = VaccineInventory::select('name')->distinct()->get();

        return view('staff.vaccination-history', compact('history', 'staffList', 'vaccineList'));
    }
    public function vaccineInventory()
    {
        $vaccines = VaccineInventory::latest()->get();
        return view('staff.vaccine-inventory', compact('vaccines'));
    }
    public function useVaccineInventory(Request $request, $id)
    {
        $vaccine = VaccineInventory::findOrFail($id);
        if ($vaccine->stock > 0) {
            $vaccine->decrement('stock', 1);
            return back()->with('success', "One dose of {$vaccine->name} deducted.");
        }
        return back()->with('error', 'Out of stock!');
    }
    public function updateVaccine(Request $request, $id)
    {
        $request->validate([
            'stock' => 'required|integer|min:0',
            'expiry_date' => 'required|date',
        ]);

        $vaccine = VaccineInventory::findOrFail($id);
        $vaccine->update([
            'stock' => $request->stock,
            'expiry_date' => $request->expiry_date,
        ]);

        return back()->with('success', "Inventory for {$vaccine->name} updated!");
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

        $inventory = VaccineInventory::where('name', $request->vaccine_name)->first();

        if (!$inventory || $inventory->stock <= 0) {
            return back()->with('error', "Insufficient stock for {$request->vaccine_name}!");
        }

        $actualBatchNo = $inventory->batch_no;
        $inventory->decrement('stock', 1);

        // 1. Create Vaccination Record
        Vaccination::create([
            'pet_id' => $id,
            'staff_id' => auth()->id(),
            'vaccine_name' => $request->vaccine_name,
            'date_administered' => $request->date_administered,
            'next_due_date' => $request->next_due_date,
            'batch_no' => $actualBatchNo,
        ]);

        // 2. Update Pet Medical Record
        $pet = Pet::findOrFail($id);
        $pet->update([
            'vaccine_type' => $request->vaccine_name,
            'last_date' => $request->date_administered,
            'next_date' => $request->next_due_date,
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
        }

        // 2. Send the Welcome Email
        Mail::send('emails.welcome', ['user' => $owner, 'password' => $plainPassword], function($message) use ($owner) {
            $message->to($owner->email)->subject('Welcome to PawCare! 🐾');
        });

        return redirect()->route('staff.pet-owners', $owner->id)
            ->with('success', 'Online account activated! Credentials sent to ' . $owner->email);
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
        // 1. Capture parameters from the request
        // 'type' is usually the category (vaccination_history vs appointments)
        // 'filter' is the specific subset (today, completed, missed)
        $reportCategory = $request->get('type');
        $filter = $request->get('filter', 'today');
        $type = ($reportCategory === 'vaccination_history') ? 'vaccination' : $filter;
        $summaryData = [];

        // --- CASE A: VACCINATION HISTORY REPORT ---
        if ($reportCategory === 'vaccination_history') {
            $query = Vaccination::with(['pet', 'staff']);
            $reportTitle = "VACCINATION HISTORY REPORT";
            $viewPath = 'staff.reports.vaccination_history_report';

            // Quick Period Filters
            if ($request->filled('period')) {
                $period = $request->get('period');
                if ($period == 'today') {
                    $query->whereDate('date_administered', today());
                } elseif ($period == 'weekly') {
                    $query->whereBetween('date_administered', [now()->startOfWeek(), now()->endOfWeek()]);
                } elseif ($period == 'monthly') {
                    $query->whereMonth('date_administered', now()->month)
                        ->whereYear('date_administered', now()->year);
                }
            }

            // Manual Date Range
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('date_administered', [$request->start_date, $request->end_date]);
            }

            // Dropdown Specific Filters
            if ($request->filled('vaccine_name')) {
                $query->where('vaccine_name', $request->vaccine_name);
            }
            if ($request->filled('staff_id')) {
                $query->where('staff_id', $request->staff_id);
            }

            $data = $query->latest('date_administered')->get();

        } else {
        // --- CASE B: APPOINTMENT REPORTS ---
        $query = Appointment::with(['pet', 'user']);
        $viewPath = 'staff.reports.staff_appointment';
        $today = today();

        // Calculate the 4 specific counts for the Summary Table
        $summaryData = [
            'date'      => $today->format('M d, Y'),
            'completed' => Appointment::whereDate('appointment_date', $today)->whereIn('status', ['done', 'completed'])->count(),
            'missed'    => Appointment::whereDate('appointment_date', $today)->where('status', 'missed')->count(),
            'cancelled' => Appointment::whereDate('appointment_date', $today)->where('status', 'cancelled')->count(),
            'total'     => Appointment::whereDate('appointment_date', $today)->count(),
        ];

        switch ($filter) {
            case 'completed':
                $query->whereIn('status', ['done', 'completed']);
                $reportTitle = "COMPLETED APPOINTMENTS REPORT";
                break;
            case 'missed':
                $query->where('status', 'missed');
                $reportTitle = "MISSED APPOINTMENTS REPORT";
                break;
            case 'today':
                // Specifically for the "Today" list view
                $query->whereDate('appointment_date', $today);
                $reportTitle = "TODAY'S APPOINTMENTS REPORT";
                break;
            case 'summary':
            default:
                // Specifically for the Executive Summary with the stats table
                $query->whereDate('appointment_date', $today);
                $reportTitle = "DAILY APPOINTMENT SUMMARY";
                break;
        }
        $data = $query->orderBy('appointment_time', 'asc')->get();
    }

    // Pass $summaryData to both PDF and View
    if ($request->has('pdf')) {
        $pdf = Pdf::loadView($viewPath, compact('data', 'reportTitle', 'type', 'summaryData', 'filter'))
                    ->setPaper('a4', 'portrait');
        return $pdf->download("PawCare_Daily_Summary.pdf");
    }

    return view($viewPath, compact('data', 'reportTitle', 'type', 'filter', 'summaryData'));
    }
    public function getPetsByOwner($userId)
{
    $pets = Pet::where('user_id', $userId)->get();
    return response()->json($pets);
}
}
