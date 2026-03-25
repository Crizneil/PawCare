<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserRequest;
use App\Models\User;
use App\Models\Pet;
use App\Models\Appointment;
use App\Models\ActivityLog;
use App\Models\Vaccination;
use App\Mail\AppointmentReminder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\WelcomeEmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TelegramAlertNotification;

class AdminController extends Controller
{
    use \App\Traits\AppointmentValidation;
    use \App\Traits\NotificationHelper;

    public function dashboard()
    {
        $totalPets = Pet::notDeceased()->count();
        $totalOwners = User::where('role', 'owner')->count();
        $totalStaff = User::where('role', 'staff')->count();

        // Vaccination Status Data for Chart
        $pets = Pet::notDeceased()->with('vaccinations')->get();
        
        $vaccinationStats = [
            'fully_vaccinated' => 0,
            'partially_vaccinated' => 0,
            'overdue' => 0,
            'due_soon' => 0,
            'unvaccinated' => 0
        ];

        foreach ($pets as $pet) {
            $status = $pet->calculated_status;
            if (isset($vaccinationStats[$status])) {
                $vaccinationStats[$status]++;
            }
        }

        // Species Data for Chart
        $speciesStats = [
            'dogs' => Pet::notDeceased()->where('species', 'Dog')->count(),
            'cats' => Pet::notDeceased()->where('species', 'Cat')->count(),
        ];

        // Upcoming Vaccinations (Next 14 days)
        $upcomingVaccinations = Vaccination::whereBetween('next_due_date', [now(), now()->addDays(14)])
            ->with(['pet', 'pet.user'])
            ->latest()
            ->get();

        $appointmentsToday = Appointment::whereDate('appointment_date', today())
            ->where('status', 'approved')
            ->count();

        $requests = UserRequest::where('status', 'pending')
            ->with(['pet', 'requester'])
            ->get();


        return view('admin.dashboard', compact(
            'totalPets',
            'totalOwners',
            'totalStaff',
            'vaccinationStats',
            'speciesStats',
            'upcomingVaccinations',
            'appointmentsToday',
            'requests'
        ));
    }

    // --- NEW METHODS FOR SIDEBAR ---

    public function appointments(Request $request)
    {
        // 1. Calculate counts for Summary Cards
        $counts = [
            'today' => Appointment::whereDate('appointment_date', today())->count(),
            'pending' => Appointment::where('status', 'pending')->count(),
            'approved' => Appointment::where('status', 'approved')->count(),
            'completed' => Appointment::whereIn('status', ['completed', 'Done'])->count(),
        ];

        // 2. Fetch Owners for the "New Appointment" Modal dropdown
        $owners = User::where('role', 'owner')->with('pets')->orderBy('name')->get();

        // 3. Handle the Table Query & Filters
        $query = Appointment::with('user');

        if ($request->status) {
            // Special case: "completed" filter should include both "completed" and "Done"
            if ($request->status === 'completed') {
                $query->whereIn('status', ['completed', 'Done']);
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->from && $request->to) {
            $query->whereBetween('appointment_date', [$request->from, $request->to]);
        }

        if ($request->owner) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->owner . '%');
            });
        }

        if ($request->pet) {
            $query->where('pet_name', 'like', '%' . $request->pet . '%');
        }

        $appointments = $query->latest()->paginate(10);

        // Pass everything to the view
        return view('admin.appointments', compact('appointments', 'counts', 'owners'));
    }

    /**
     * API: Get all appointments for the FullCalendar master view.
     */
    public function getAppointmentsApi(Request $request)
    {
        // Filter out cancelled appointments for the calendar view
        $appointments = Appointment::with(['user', 'pet'])
            ->whereNotIn('status', ['cancelled', 'Cancelled'])
            ->get();

        $events = $appointments->map(function ($appt) {
            $owner = $appt->user;

            // 1. Combine the granular address fields from the User model
            // Note: Using 'house_no' as per your recent database update
            $fullAddress = $owner
                ? "{$owner->house_no} {$owner->street}, Brgy. {$owner->barangay}, {$owner->city}"
                : 'Not Provided';

            // 2. Colors based on status
            $colors = [
                'pending'   => '#fd7e14', // Orange
                'approved'  => '#0d6efd', // Blue
                'completed' => '#198754', // Green
                'Done'      => '#198754',
                'rejected'  => '#6c757d', // Gray
            ];

            return [
                'id'              => $appt->id,
                'title'           => ($appt->pet->name ?? $appt->pet_name) . ' (' . ucfirst($appt->service_type) . ')',
                'start'           => $appt->appointment_date . 'T' . $appt->appointment_time,
                'backgroundColor' => $colors[$appt->status] ?? '#6c757d',
                'borderColor'     => $colors[$appt->status] ?? '#6c757d',
                'textColor'       => '#ffffff',
                'extendedProps'   => [
                    'owner_name'    => $owner ? $owner->name : 'Walk-in',
                    'owner_phone'   => $owner ? $owner->phone : 'N/A',
                    'owner_address' => $fullAddress, // <--- This sends the address to the calendar
                    'species'       => ucfirst($appt->species),
                    'status'        => ucfirst($appt->status)
                ]
            ];
        });

        return response()->json($events);
    }

    /**
     * API: Update appointment date/time from Master Calendar Drag-and-Drop
     */
    public function updateDragAndDrop(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid date/time format.'], 400);
        }

        $appointment = Appointment::findOrFail($id);

        if (in_array(strtolower($appointment->status), ['completed', 'done', 'cancelled', 'rejected'])) {
            return response()->json(['success' => false, 'message' => 'Cannot reschedule a closed appointment.'], 403);
        }

        $oldDate = Carbon::parse($appointment->appointment_date)->format('M d, Y') . ' at ' . Carbon::parse($appointment->appointment_time)->format('h:i A');
        $newDate = Carbon::parse($request->appointment_date)->format('M d, Y') . ' at ' . Carbon::parse($request->appointment_time)->format('h:i A');

        $appointment->update([
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
        ]);

        ActivityLog::record(
            'RESCHEDULE_APPOINTMENT',
            "Admin rescheduled Appointment #APT-{$appointment->id} from {$oldDate} to {$newDate} via Calendar Drag-and-Drop."
        );

        return response()->json(['success' => true, 'message' => 'Appointment successfully rescheduled.']);
    }

    public function approve($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update(['status' => 'approved']);

        ActivityLog::record('APPROVE', 'Approved appointment for pet: ' . $appointment->pet_name);

        // Send approval email
        Mail::to($appointment->user->email)
            ->send(new AppointmentReminder($appointment, 'approved'));

        // Send Telegram alert
        try {
            $this->sendTelegramNotification($appointment, 'appointment_approved');
        } catch (\Throwable $e) {
            \Log::error('Failed to send Telegram approval alert: ' . $e->getMessage());
        }

        return back()->with('success', 'Appointment Approved and owner notified!');
    }

    public function createAppointment()
    {
        // Fetch owners so the dropdown menu in your form actually works
        $owners = User::where('role', 'owner')->with('pets')->orderBy('name')->get();

        return view('admin.appointment-create', compact('owners'));
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $appointment = Appointment::findOrFail($id);
        $appointment->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason
        ]);

        ActivityLog::record('REJECT', "Rejected appointment #{$id} for {$appointment->pet_name}. Reason: {$request->rejection_reason}");

        // Send rejection email
        Mail::to($appointment->user->email)
            ->send(new AppointmentReminder($appointment, 'rejected'));

        // Send Telegram alert
        try {
            $this->sendTelegramNotification($appointment, 'appointment_rejected');
        } catch (\Throwable $e) {
            \Log::error('Failed to send Telegram rejection alert: ' . $e->getMessage());
        }

        return back()->with('success', 'Appointment rejected and owner notified.');
    }

    public function reschedule(Request $request, $id)
    {
        $request->validate([
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
        ]);

        $appointment = Appointment::findOrFail($id);
        $oldDate = $appointment->appointment_date;

        $appointment->update([
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'status' => 'rescheduled',
        ]);

        ActivityLog::record('RESCHEDULE', "Moved appointment #{$id} from {$oldDate} to {$request->appointment_date}");

        // Send reschedule email
        Mail::to($appointment->user->email)
            ->send(new AppointmentReminder($appointment, 'rescheduled'));

        // Send Telegram alert
        try {
            $this->sendTelegramNotification($appointment, 'appointment_rescheduled');
        } catch (\Throwable $e) {
            \Log::error('Failed to send Telegram reschedule alert: ' . $e->getMessage());
        }

        return back()->with('success', 'Appointment rescheduled and owner notified.');
    }

    public function cancel($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update(['status' => 'cancelled']);

        return back()->with('success', 'Appointment cancelled.');
    }

    public function markDone($id)
    {
        $appointment = Appointment::with('user')->findOrFail($id);

        $current = strtolower((string) $appointment->status);
        if (in_array($current, ['done', 'completed', 'cancelled', 'rejected'], true)) {
            return back()->with('error', 'This appointment is already closed.');
        }

        // 1. Process Medical Records if it's a Vaccination/Deworming
        $finalName = $appointment->vaccine_name ?? $appointment->service_type;

        if ($this->isVaccinationService($appointment->service_type)) {
            $pet = Pet::find($appointment->pet_id);
            if ($pet) {
                $interval = $this->getServiceInterval($finalName);
                $batchNo = 'ADMIN-' . date('Ymd');

                // Update Pet's Digital Medical Card
                $pet->update([
                    'vaccine_type' => $finalName,
                    'last_date' => now(),
                    'next_date' => now()->addDays($interval),
                ]);

                // The system now requires staff/admins to log vaccinations manually.
                // We no longer automatically create a Vaccination record here.

                // Update Appointment fields for record keeping
                $appointment->batch_no = $batchNo;
                $appointment->vaccine_name = $finalName;
                $appointment->next_due_date = now()->addDays($interval);
                $appointment->administered_by = auth()->user()->name;
            }
        }

        // Mark as completed (use "Done" to match existing staff/admin colors/labels)
        $appointment->update(['status' => 'Done']);

        ActivityLog::record(
            'MARK_DONE',
            'Marked appointment as Done and logged medical records for pet: ' . $appointment->pet_name
        );

        return back()->with('success', 'Appointment marked as Done.');
    }
    public function owners()
    {
        $owners = User::where('role', 'owner')->latest()->paginate(10);
        return view('admin.owners', compact('owners'));
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
        $rawPassword = Str::random(8);

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

            // you can concatenate them like this:
            'address' => "{$request->house_no} {$request->street}, {$request->barangay}, {$request->city}, {$request->province}",
        ]);

        // 4. Record Activity
        ActivityLog::record(
            'CREATE_OWNER',
            'Admin successfully created a new owner account for: ' . $user->name
        );

        // 5. Send Automated Welcome Email
        try {
            Mail::to($user->email)->send(new WelcomeEmail($user, $rawPassword));

            // --- NEW: Send Telegram Notification to Clinic Owner ---
            $this->sendTelegramNotification($user, 'account_created');

        } catch (\Throwable $e) {
            return back()->with('error', 'Owner registered, but email failed! Password is: ' . $rawPassword);
        }

        return back()->with('success', 'New owner account successfully registered!');
    }

    public function logs(Request $request)
    {
        $search = $request->input('search');
        $view = $request->input('view', 'active'); // 'active' or 'archived'

        $query = ActivityLog::with('user');

        // Search Logic
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                            ->orWhere('role', 'like', "%{$search}%");
                    });
            });
        }

        // Toggle between Active and Archived (Soft Deleted)
        if ($view === 'archived') {
            $logs = $query->onlyTrashed()->latest()->paginate(10);
        } else {
            $logs = $query->latest()->paginate(10);
        }

        return view('admin.logs', compact('logs', 'view', 'search'));
    }

    // Method to Archive (Soft Delete)
    public function archiveLogs()
    {
        ActivityLog::whereNotNull('id')->delete();
        return back()->with('success', 'Logs moved to archive.');
    }

    public function profile()
    {
        $admin = Auth::user();
        return view('admin.profile', compact('admin'));
    }
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:11',
            'gender' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'house_no' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'barangay' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
        ]);

        $data = $request->all();

        // Handle File/Base64 Upload
        if ($request->filled('profile_image_base64')) {
            // Delete old
            if ($user->profile_image) Storage::delete('public/' . $user->profile_image);
            
            $imageData = $request->input('profile_image_base64');
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
            $imageName = 'profiles/' . uniqid() . '.png';
            Storage::disk('public')->put($imageName, base64_decode($imageData));
            $data['profile_image'] = $imageName;
        } elseif ($request->hasFile('profile_image')) {
            // Delete old image if it exists
            if ($user->profile_image) {
                Storage::delete('public/' . $user->profile_image);
            }

            $path = $request->file('profile_image')->store('profiles', 'public');
            $data['profile_image'] = $path;
        }

        $user->update($data);

        return back()->with('success', 'Profile updated successfully!');
    }

    // Restore a single log
    public function restoreLog($id)
    {
        $log = ActivityLog::onlyTrashed()->findOrFail($id);
        $log->restore();

        return back()->with('success', 'Log restored successfully!');
    }

    // Restore all archived logs at once
    public function restoreAllLogs()
    {
        ActivityLog::onlyTrashed()->restore();

        return back()->with('success', 'All logs have been restored.');
    }

    public function petRecords(Request $request)
    {
        $view = $request->input('view', 'active'); // 'active' or 'archived'
        $query = Pet::with('user');

        $petId = $request->input('pet_id');
        $search = $request->input('general_search');

        // 1. URL Extraction Logic (for QR scans)
        if ($search && filter_var($search, FILTER_VALIDATE_URL)) {
            $search = collect(explode('/', $search))->last();
        }

        // 2. Search Logic
        if ($petId) {
            $query->withTrashed()->where(function ($q) use ($petId) {
                $q->where('id', $petId)
                    ->orWhere('pet_id', $petId);
            });
        } elseif ($search) {
            // Added 'ADMIN-' to the check to match your manual appointments
            if (str_starts_with(strtoupper($search), 'PC-') ||
                str_starts_with(strtoupper($search), 'WALK-') ||
                str_starts_with(strtoupper($search), 'ADMIN-')) {
                $query->withTrashed()->where('pet_id', 'like', "%{$search}%");
            } else {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('breed', 'like', "%{$search}%")
                        ->orWhere('owner', 'like', "%{$search}%");
                });
            }
        }

        // 3. Status Filtering (Crucial: Keep this part!)
        if (!$petId && !$search) {
            if ($view === 'archived') {
                $query->withTrashed()->where(function ($q) {
                    $q->whereIn('status', ['DECEASED', 'INACTIVE'])
                    ->orWhereNotNull('deleted_at');
                });
            } else {
                $query->notDeceased();
            }
        }

        // 4. Results and View
        $pets = $query->latest()->paginate(10)->appends($request->all());
        $owners = User::where('role', 'owner')->with('pets')->orderBy('name')->get();

        return view('admin.pet-records', compact('pets', 'owners', 'view'));
    }

    /**
     * Restore a pet that was marked as DECEASED (Status Only)
     */
    public function restoreDeceasedPet($id)
    {
        $pet = Pet::findOrFail($id);

        if (!in_array($pet->status, ['DECEASED', 'INACTIVE'])) {
            return back()->with('error', 'This pet is not marked as deceased or inactive.');
        }

        $pet->update(['status' => 'Verified']); // Or ACTIVE

        ActivityLog::record(
            'RESTORE',
            "Restored inactive/deceased pet record for: {$pet->name}"
        );

        return back()->with('success', "Pet record for {$pet->name} has been restored to active status.");
    }
    // --- ADD THESE METHODS FOR PET MANAGEMENT ---


    public function storePet(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'gender' => 'required',
            'species' => 'required|string|in:Dog,Cat',
            'breed' => 'nullable|string|max:255',
            'birthdate' => 'required|date|before_or_equal:today',
            'image' => 'nullable|image|max:2048',
            'image_base64' => 'nullable|string',
        ]);

        $imagePath = null;
        if ($request->filled('image_base64')) {
            $imageData = $request->input('image_base64');
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
            $imageName = 'profiles/' . uniqid() . '.png';
            Storage::disk('public')->put($imageName, base64_decode($imageData));
            $imagePath = $imageName;
        } elseif ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('profiles', 'public');
        }

        $year = date('Y');
        $count = Pet::withTrashed()->count() + 1;
        $unique_id = 'PC-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $pet = Pet::create([
            'user_id' => $request->user_id,
            'pet_id' => $unique_id,
            'name' => $request->name,
            'species' => $request->species,
            'breed' => $request->breed ?? 'Unknown',
            'birthday' => $request->birthdate,
            'gender' => $request->gender, // Need to pass default gender to satisfy schema
            'owner' => User::find($request->user_id)->name ?? 'Unknown',
            'image_url' => $imagePath,
            'status' => 'Verified', // Pets added by Admin are automatically verified
        ]);

        ActivityLog::record(
            'CREATE_PET',
            "Admin successfully registered a new pet ({$pet->name}) for owner ID: {$pet->user_id}"
        );

        // --- NEW: Send Telegram Notification to Clinic Owner ---
        try {
            $this->sendTelegramNotification($pet, 'pet_registered');
        } catch (\Throwable $e) {
            \Log::error('Failed to send Telegram pet registration alert (Admin): ' . $e->getMessage());
        }

        return back()->with('success', 'Pet successfully registered! Pet ID: ' . $unique_id);
    }


    public function updatePet(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'breed' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
            'image_base64' => 'nullable|string',
        ]);

        $pet = Pet::findOrFail($id);

        // Store old name for logging
        $oldName = $pet->name;

        $oldStatus = $pet->status;
        $newStatus = $request->status ?? $pet->status;

        $updateData = [
            'name' => $request->name,
            'breed' => $request->breed,
            'status' => $newStatus,
        ];

        if ($request->filled('image_base64')) {
            if ($pet->image_url) Storage::delete('public/' . $pet->image_url);
            $imageData = $request->input('image_base64');
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
            $imageName = 'profiles/' . uniqid() . '.png';
            Storage::disk('public')->put($imageName, base64_decode($imageData));
            $updateData['image_url'] = $imageName;
        } elseif ($request->hasFile('image')) {
            if ($pet->image_url) Storage::delete('public/' . $pet->image_url);
            $updateData['image_url'] = $request->file('image')->store('profiles', 'public');
        }

        $pet->update($updateData);

        if ($oldStatus !== 'DECEASED' && $newStatus === 'DECEASED') {
            session()->flash('status_changed', [
                'type' => 'DECEASED',
                'pet_name' => $pet->name
            ]);
        }

        ActivityLog::record(
            'UPDATE',
            "Updated pet record: Changed {$oldName} info."
        );

        return back()->with('success', 'Pet record updated successfully!');
    }


    public function destroyPet($id)
    {
        $pet = Pet::findOrFail($id);
        $petName = $pet->name;

        ActivityLog::record(
            'DELETE',
            'Deleted pet record for: ' . $petName
        );

        $pet->delete();

        return back()->with('success', "Record for {$petName} has been removed.");
    }

    public function enroll(Request $request)
    {
        $request->validate([
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email',
            'pet_name' => 'required|string|max:255',
            'breed' => 'required|string|max:255',
            'next_date' => 'required|date',
        ]);

        $owner = User::firstOrCreate(
            ['email' => $request->owner_email],
            [
                'name' => $request->owner_name,
                'password' => Hash::make('password'),
                'role' => 'owner',
                'house_number' => 'N/A',
                'street' => 'N/A',
                'barangay' => 'N/A',
                'city' => 'City of Meycauayan',
                'province' => 'Bulacan',
            ]
        );

        $year = date('Y');
        $count = Pet::withTrashed()->count() + 1;
        $unique_id = 'PC-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        Pet::create([
            'user_id' => $owner->id,
            'pet_id' => $unique_id,
            'name' => $request->pet_name,
            'species' => 'Dog',
            'breed' => $request->breed,
            'birthday' => now()->toDateString(),
            'gender' => 'Unknown',
            'owner' => $owner->name,
            'next_date' => $request->next_date,
            'status' => 'Verified',
        ]);

        return back()->with('success', 'Pet enrolled successfully! ID: ' . $unique_id);
    }

    public function employees()
    {
        $staff = User::where('role', 'staff')->latest()->paginate(10);

        return view('admin.employees', compact('staff'));
    }

    public function storeStaff(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'staff', // This matches your RoleMiddleware
            'house_number' => 'Clinic',
            'street' => 'MacArthur Hwy',
            'barangay' => 'Clinic Brgy',
            'city' => 'City of Meycauayan',
            'province' => 'Bulacan',
        ]);
        ActivityLog::record(
            'CREATE',
            'Created new staff account: ' . $request->name
        );


        return redirect()->route('admin.employees')->with('success', 'Staff account created successfully!');
    }
    /**
     * Update the specified staff member
     */
    public function updateStaff(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $staff = User::findOrFail($id);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $staff->update($data);

        ActivityLog::record(
            'UPDATE',
            'Updated staff account: ' . $staff->name
        );


        return back()->with('success', 'Staff member updated successfully!');
    }


    public function destroyStaff($id)
    {
        $staff = User::findOrFail($id);

        if ($staff->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account!');
        }

        ActivityLog::record(
            'DELETE',
            'Deleted staff account: ' . $staff->name
        );

        $staff->delete();

        return back()->with('success', 'Staff account removed successfully.');
    }

    public function storeAppointment(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'pet_name' => 'required|string|max:255',
                'species' => 'required|string',
                'gender' => 'required|string',
                'breed' => 'required|string',
                'other_breed' => 'nullable|string|required_if:breed,Other',
                'birthday' => 'required|date',
                'appointment_date' => 'required|date',
                'appointment_time' => 'required',
                'service_type' => 'required|string',
            ]);

            $user = User::findOrFail($request->user_id);

            // 1. Try to find existing pet by ID or Name
            $pet = null;
            if (is_numeric($request->pet_name)) {
                $pet = Pet::where('user_id', $user->id)->where('id', $request->pet_name)->first();
            }

            if (!$pet) {
                $pet = Pet::where('user_id', $user->id)
                    ->where('name', $request->pet_name)
                    ->first();
            }

            // If pet doesn't exist, create it with full details provided
            if (!$pet) {
                $finalBreed = ($request->breed === 'Other') ? $request->other_breed : $request->breed;

                $pet = Pet::create([
                    'user_id' => $user->id,
                    'pet_id' => 'ADMIN-' . strtoupper(substr(uniqid(), -5)),
                    'name' => $request->pet_name,
                    'species' => $request->species,
                    'gender' => $request->gender,
                    'breed' => $finalBreed,
                    'birthday' => $request->birthday,
                    'owner' => $user->name,
                    'status' => 'ACTIVE',
                ]);
            }

            // --- NEW: Centralized Service & Vaccination Eligibility Validation ---
            $error = $this->checkServiceEligibility($pet->id, $request->service_type, $request->appointment_date);
            if ($error) {
                return back()->withErrors(['service_type' => $error])->withInput();
            }
            // --- End of Validation ---

            // Standardize time format for database
            $formattedTime = date('H:i:s', strtotime($request->appointment_time));

            // Double Booking Prevention Check
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $user, $pet, $formattedTime) {
                $existing = Appointment::where('appointment_date', $request->appointment_date)
                    ->where('appointment_time', $formattedTime)
                    ->whereNotIn('status', ['cancelled', 'rejected'])
                    ->lockForUpdate()
                    ->exists();

                if ($existing) {
                    return back()->withErrors(['appointment_time' => 'Sorry, this time slot has just been booked by someone else. Please choose another time.'])->withInput();
                }

                $appointment = Appointment::create([
                    'user_id' => $user->id,
                    'pet_id' => $pet->id,
                    'pet_name' => $pet->name,
                    'species' => $pet->species,
                    'gender' => $request->gender,
                    'appointment_date' => $request->appointment_date,
                    'appointment_time' => $formattedTime,
                    'service_type' => $request->service_type,
                    'status' => 'approved', // Admin bookings are auto-approved
                ]);

                // --- NEW: Send Telegram Notification to Admin/Owner ---
                try {
                    $this->sendTelegramNotification($appointment, 'appointment_new');
                } catch (\Throwable $e) {
                    \Log::error('Failed to send Telegram notification (Admin): ' . $e->getMessage());
                }

                return back()->with('success', 'Appointment scheduled successfully!');
            });
        } catch (\Exception $e) {
            // LOG THE REAL ERROR to bypass the failing error renderer
            @file_put_contents(base_path('storage/logs/appt_error.log'), "[" . date('Y-m-d H:i:s') . "] " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n", FILE_APPEND);
            return back()->withErrors(['error' => 'An error occurred while saving the appointment. Please check the logs.'])->withInput();
        }
    }

    public function archive(Request $request)
    {
        $tab = $request->input('tab', 'pets');
        $search = $request->input('search');

        $data = [];
        if ($tab === 'pets') {
            // Query both soft-deleted pets AND pets with inactive/deceased status
            $query = Pet::withTrashed()->with('user')->where(function ($q) {
                $q->onlyTrashed() // Records with deleted_at
                ->orWhereIn('status', ['DECEASED', 'INACTIVE']); // Records with archived status
            });
            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }
            $data = $query->latest()->paginate(10);
        } elseif ($tab === 'staff') {
            $query = User::onlyTrashed()->where('role', 'staff');
            if ($search)
                $query->where('name', 'like', "%{$search}%");
            $data = $query->latest()->paginate(10);
        }

        return view('admin.archive', compact('data', 'tab', 'search'));
    }

    public function restorePet($id)
    {
        // Use withTrashed() so we can find BOTH soft-deleted and status-archived pets
        $pet = Pet::withTrashed()->findOrFail($id);

        if ($pet->trashed()) {
            // Handle actual Soft Deletes
            $pet->restore();
        }

        // This handles both restored pets and Deceased/Inactive pets
        $pet->update(['status' => 'Verified']);

        ActivityLog::record(
            'RESTORE',
            "Restored pet record and updated status for: " . $pet->name
        );

        return back()->with('success', "Pet record for {$pet->name} has been restored successfully.");
    }

    public function restoreStaff($id)
    {
        $staff = User::onlyTrashed()->findOrFail($id);
        $staff->restore();

        ActivityLog::record(
            'RESTORE',
            "Restored staff account: " . $staff->name
        );

        return back()->with('success', "Staff account for {$staff->name} restored.");
    }

    public function sendEmailReminder($appointment)
    {
        $details = [
            'name' => $appointment->user->name,
            'pet' => $appointment->pet_name,
            'date' => $appointment->appointment_date,
        ];

        Mail::to($appointment->user->email)->send(new AppointmentReminder($details));

        return back()->with('success', 'Email reminder sent!');
    }
    public function showOwner(Request $request, $id)
    {
        // Check if we are viewing a Walk-in (based on the 'type' parameter from your link)
        if ($request->query('type') === 'walkin') {
            // For walk-ins, the $id is actually the PET ID.
            // We find the pet and treat its columns as the 'owner' data.
            $owner = Pet::findOrFail($id);

            // We wrap it in an object so the Blade view doesn't break
            return view('admin.owner-profile', [
                'owner' => $owner,
                'is_walkin' => true
            ]);
        }

        // Standard case: View a registered Owner account
        $owner = User::with('pets')->where('role', 'owner')->findOrFail($id);

        return view('admin.owner-profile', [
            'owner' => $owner,
            'is_walkin' => false
        ]);
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
