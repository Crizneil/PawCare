<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppointmentConfirmedEmail;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TelegramAlertNotification;

class PetController extends Controller
{
    use \App\Traits\AppointmentValidation;

    public function index(Request $request)
    {
        $query = Pet::with('user');

        // This must match the parameter name sent by adminSearch redirect
        $searchId = $request->query('pet_id') ?? $request->query('search');

        if ($searchId) {
            $query->where('pet_id', $searchId);
        }

        // General search (name, breed, owner) - only if no specific ID search is active
        if ($request->has('general_search')) {
            $search = $request->general_search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('breed', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $pets = $query->latest()->get();
        return view('admin.pet-records', compact('pets'));
    }

    /**
     * PET OWNER VIEW: Only sees pets belonging to the logged-in user
     */
    public function ownerDashboard()
    {
        // Get only pets of logged in owner
        $pets = Pet::where('user_id', Auth::id())->latest()->get();

        // Pet Count
        $petCount = $pets->count();

        // Fetch real upcoming appointment
        $latestAppointment = Appointment::where('user_id', Auth::id())
            ->whereDate('appointment_date', '>=', now()->toDateString())
            ->whereIn('status', ['pending', 'approved', 'rescheduled'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->first();

        $nextAppointment = $latestAppointment
            ? Carbon::parse($latestAppointment->appointment_date)->format('M d, Y')
            : 'No Appointment';
        $appointmentStatus = $latestAppointment
            ? ucfirst($latestAppointment->status)
            : '';

        // Upcoming Vaccine Due
        $nextVaccineDate = optional(
            $pets->whereNotNull('next_date')->sortBy('next_date')->first()
        )->next_date;

        $nextVaccine = $nextVaccineDate
            ? Carbon::parse($nextVaccineDate)->format('M d, Y')
            : 'No Due Vaccine';

        // Reminder Alert Logic
        $vaccineReminder = null;

        if ($nextVaccineDate) {
            $daysRemaining = now()->startOfDay()->diffInDays(Carbon::parse($nextVaccineDate)->startOfDay(), false);

            if ($daysRemaining <= 7 && $daysRemaining >= 0) {
                $vaccineReminder = 'Rabies Vaccine due in ' . $daysRemaining . ' day(s)';
            }
        }

        // Clinic Announcement
        $announcement = 'Free Anti-Rabies Vaccination this Saturday!';

        return view('pet-owner.dashboard', compact(
            'pets',
            'petCount',
            'nextAppointment',
            'appointmentStatus',
            'nextVaccine',
            'vaccineReminder',
            'announcement'
        ));
    }

    public function appointments()
    {
        $today = now()->toDateString();
        $totalSlots = 20; // Clinic capacity (e.g., 20)

        // 1. Calculate stats for the Status Badge (Today)
        $totalBookedToday = Appointment::where('appointment_date', $today)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->count();

        $userBookedToday = Appointment::where('appointment_date', $today)
            ->where('user_id', auth()->id())
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->count();

        // 2. Fetch the owner's appointment history
        $appointments = Appointment::where('user_id', auth()->id())
            ->where('status', '!=', 'cancelled')
            ->latest()
            ->paginate(10);

        return view('pet-owner.appointments', compact(
            'appointments',
            'totalBookedToday',
            'userBookedToday',
            'totalSlots'
        ));
    }
    public function cancelAppointment($id)
    {
        // Find appointment and ensure it belongs to the logged-in user
        $appointment = Appointment::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Check if it's already completed or already cancelled
        if ($appointment->status === 'completed' || $appointment->status === 'Done') {
            return back()->with('error', 'Cannot cancel a completed appointment.');
        }

        $appointment->update(['status' => 'cancelled']);

        // --- NEW: Send Telegram Notification to Admin/Owner ---
        try {
            Notification::route('telegram', env('TELEGRAM_CHAT_ID'))
                ->notify(new TelegramAlertNotification($appointment, 'appointment_cancelled'));
        } catch (\Exception $e) {
            \Log::error('Failed to send Telegram cancellation alert: ' . $e->getMessage());
        }

        return back()->with('success', 'Appointment cancelled successfully.');
    }

    /**
     * API: Get available slots per day (for user calendar)
     */
    public function getAvailableSlots(Request $request)
    {
        $start = Carbon::parse($request->start)->format('Y-m-d');
        $end = Carbon::parse($request->end)->format('Y-m-d');
        $ownerId = auth()->id();

        // 1. Fetch ALL appointments for the range (including completed ones)
        // We only exclude truly 'dead' appointments like cancelled/rejected
        $appointments = Appointment::whereBetween('appointment_date', [$start, $end])
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->get();

        $bookedSlots = [];
        $ownerBookedDates = [];

        foreach ($appointments as $appt) {
            $date = date('Y-m-d', strtotime($appt->appointment_date));
            $time = date('H:i', strtotime($appt->appointment_time));
            $status = strtolower($appt->status);

            // 2. OWNER LIMIT LOGIC:
            // We count ALL appointments (even 'done') toward their daily limit of 2
            if ($appt->user_id == $ownerId) {
                if (!isset($ownerBookedDates[$date])) {
                    $ownerBookedDates[$date] = [];
                }
                $ownerBookedDates[$date][] = [
                    'id' => $appt->id,
                    'pet_id' => $appt->pet_id,
                    'status' => $status
                ];
            }

            // 3. CLINIC CAPACITY LOGIC:
            // Only count 'active' appointments toward the 16-slot clinic limit.
            // Completed/Done appointments no longer "block" a time slot for others.
            if (!in_array($status, ['done', 'completed'])) {
                $bookedSlots[$date][] = $time;

                if (strtolower($appt->service_type) === 'kapon') {
                    $nextSlot = date('H:i', strtotime($appt->appointment_time . ' +30 minutes'));
                    $bookedSlots[$date][] = $nextSlot;
                }
            }
        }

        return response()->json([
            'booked_slots' => $bookedSlots,
            'owner_booked_dates' => (object)$ownerBookedDates,
            'debug' => [
                'user_id' => auth()->id(),
                'owner_count' => count($ownerBookedDates),
                'range' => [$start, $end]
            ]
        ]);
    }

    public function book(Request $request)
    {
        $request->validate([
            'pet_id' => 'required|exists:pets,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|string',
            'service_type' => 'required|string',
            'address' => 'required|string'
        ]);

        // Prevent bookings on clinic closed days (Saturday & Sunday)
        $appointmentDate = Carbon::parse($request->appointment_date);
        if ($appointmentDate->isWeekend()) {
            return back()
                ->withErrors(['appointment_date' => 'The clinic is closed on Saturdays and Sundays. Please choose a weekday.'])
                ->withInput();
        }

        $pet = Pet::where('id', $request->pet_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Business rule: only VERIFIED / ACTIVE pets can be booked
        if (!in_array($pet->status, ['ACTIVE', 'Verified'], true)) {
            return back()
                ->withErrors(['pet_id' => 'Only verified pets with active status can be booked for appointments.'])
                ->withInput();
        }

        // --- NEW: Anti-Rabies Validation ---
        if ($request->service_type === 'Anti-Rabies') {
            $error = $this->checkAntiRabiesEligibility($pet->id, $request->appointment_date);
            if ($error) {
                return back()->withErrors(['service_type' => $error])->withInput();
            }
        }

        // Double Booking Prevention
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $pet) {
            // Convert time format for database strictly before checking (e.g., "08:00" to "08:00:00")
            $formattedTime = date('H:i:s', strtotime($request->appointment_time));
            // Check if the user already has a pending appointment for this pet to avoid spam
            $duplicate = Appointment::where('pet_id', $pet->id)
                ->where('status', 'pending')
                ->exists();

            if ($duplicate) {
                return back()->withErrors(['pet_id' => 'This pet already has a pending appointment.']);
            }

            $existing = Appointment::where('appointment_date', $request->appointment_date)
                ->where('appointment_time', $formattedTime)
                ->whereNotIn('status', ['cancelled', 'rejected'])
                ->lockForUpdate()
                ->exists();

            if ($existing) {
                return back()->withErrors(['appointment_time' => 'Sorry, this time slot has just been booked by someone else. Please choose another time.'])->withInput();
            }

            $appointment = Appointment::create([
                'user_id' => auth()->id(),
                'pet_id' => $pet->id,
                'pet_name' => $pet->name,
                'species' => $pet->species,
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $formattedTime,
                'service_type' => $request->service_type,
                'status' => 'pending',
                'address' => $request->address
            ]);

            // Send the appointment confirmation email to the owner
            try {
                Mail::to(auth()->user()->email)->send(new AppointmentConfirmedEmail($appointment));
                
                // --- NEW: Send Telegram Notification to Admin/Owner ---
                Notification::route('telegram', env('TELEGRAM_CHAT_ID'))
                    ->notify(new TelegramAlertNotification($appointment, 'appointment_new'));

            } catch (\Exception $e) {
                // Log the error or handle it silently so it doesn't interrupt the booking process
                \Log::error('Failed to send notifications: ' . $e->getMessage());
            }

            return redirect()->route('pet-owner.appointments')->with('success', 'Appointment requested!');
        });
    }
    public function petOwners()
    {
        return view('admin.pet-owners');
    }

    public function vaccinations()
    {
        return view('admin.vaccinations');
    }

    public function settings()
    {
        return view('admin.settings');
    }

    public function store(Request $request)
    {
        // 1. Validation - Notice 'birthdate' vs 'birthday'
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'species' => 'required',
            'gender' => 'required|in:Male,Female',
            'breed' => 'required',
            'other_breed' => 'required_if:breed,Other', // Matches your modal input name
            'birthdate' => 'required|date',
            'user_id' => 'required|exists:users,id', // Changed from birthday to match modal
        ]);

        // 2. Handle the "Other" Breed logic
        $finalBreed = ($request->breed === 'Other')
            ? $request->other_breed
            : $request->breed;

        // 3. Handle File/Base64 Upload (Save RELATIVE path for consistency)
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

        // 4. Determine the Owner
        // If the admin is adding it, use the selected user_id.
        // If the owner is adding it, use Auth::id().
        $ownerId = $request->user_id ?? Auth::id();
        $ownerRecord = \App\Models\User::find($ownerId);

        // 5. Create the pet record
        $pet = Pet::create([
            'pet_id' => 'PC-2026-' . rand(1000, 9999),
            'user_id' => $ownerId,
            'name' => $request->name,
            'gender' => $request->gender, // This will now work correctly
            'species' => $request->species,
            'birthday' => $request->birthdate, // Map birthdate from form to birthday in DB
            'breed' => $finalBreed,
            'owner' => $ownerRecord->name ?? 'Unknown Owner',
            'image_url' => $imagePath,
            'status' => 'ACTIVE',
            'last_date' => now(),
            'vaccine_type' => 'None',
        ]);

        // --- NEW: Send Telegram Notification to Clinic Owner ---
        try {
            Notification::route('telegram', env('TELEGRAM_CHAT_ID'))
                ->notify(new TelegramAlertNotification($pet, 'pet_registered'));
        } catch (\Exception $e) {
            \Log::error('Failed to send Telegram pet registration alert: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Pet registered successfully!');
    }

    public function destroy($id)
    {
        Pet::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }
    public function petRecords()
    {
        // Fetch only pets belonging to the logged-in user
        $pets = Pet::where('user_id', Auth::id())
            ->notDeceased()
            ->with('latestVaccination')
            ->latest()
            ->get();

        return view('pet-owner.pet-records', compact('pets'));
    }

    /**
     * Single View: Shows the printable Digital ID Card for one specific pet
     */
    public function showDigitalId($id)
    {
        // Find pet by ID but ensure it belongs to the logged-in user for security
        $pet = Pet::where('id', $id)
            ->where('user_id', Auth::id())
            ->with('vaccinations') // Assuming you have this relationship
            ->firstOrFail();

        // Points to resources/views/pet-owner/digital-id.blade.php
        return view('pet-owner.digital-id', compact('pet'));
    }
    public function publicProfile($pet_id)
    {
        // Search by the custom pet_id (e.g., PC-2026-8929) not the database auto-increment ID
        $pet = Pet::where('pet_id', $pet_id)->firstOrFail();

        return view('public.pet_verify', compact('pet'));
    }

    /**
     * Profile page for the owner
     */
    public function profile()
    {
        return view('pet-owner.profile');
    }

    /**
     * Update Owner Password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        // Verify the old password matches the database hash
        if (!Hash::check($request->old_password, $user->password)) {
            return back()->with('error', 'The provided old password does not match our records.');
        }

        // Update the password
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        // Log the security event
        ActivityLog::record(
            'UPDATE_PASSWORD',
            "{$user->name} changed their account password."
        );

        return back()->with('success', 'Password successfully updated! You can now use your new password next time you log in.');
    }

    // --- Private Helper Methods ---
    public function adminSearch(Request $request)
    {
        $input = $request->input('search');

        if (!$input) {
            return back()->with('error', 'Please scan a QR code or enter an ID.');
        }

        // 1. If it's a full URL, extract the last segment
        if (filter_var($input, FILTER_VALIDATE_URL)) {
            $petId = basename(parse_url($input, PHP_URL_PATH));
        } else {
            // 2. Otherwise use the input directly (could be a custom ID or primary ID)
            $petId = $input;
        }

        // Try finding by internal ID or pet_id (whichever exists in schema)
        $pet = Pet::withTrashed()->where(function ($q) use ($petId) {
            $q->where('id', $petId)
                ->orWhere('pet_id', 'like', "%{$petId}%");
        })->first();

        if ($pet) {
            // Forward to the pet records with the primary DB ID for unambiguous filtering
            return redirect()->route('admin.pet-records', ['pet_id' => $pet->id]);
        }

        return back()->with('error', 'Pet record not found for: ' . $petId);
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'species' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'image_base64' => 'nullable|string',
        ]);

        $pet = Pet::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        if ($request->filled('image_base64')) {
            if ($pet->image_url) Storage::disk('public')->delete($pet->image_url);
            $imageData = $request->input('image_base64');
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
            $imageName = 'profiles/' . uniqid() . '.png';
            Storage::disk('public')->put($imageName, base64_decode($imageData));
            $pet->image_url = $imageName;
        } elseif ($request->hasFile('image')) {
            if ($pet->image_url) Storage::disk('public')->delete($pet->image_url);
            $pet->image_url = $request->file('image')->store('profiles', 'public');
        }

        $oldStatus = $pet->status;
        $newStatus = $request->status ?? $pet->status;

        $pet->name = $request->name;
        $pet->species = $request->species;
        $pet->breed = $request->breed;
        $pet->status = $newStatus;
        $pet->save();

        if ($oldStatus !== 'DECEASED' && $newStatus === 'DECEASED') {
            session()->flash('status_changed', [
                'type' => 'DECEASED',
                'pet_name' => $pet->name
            ]);
        }

        return back()->with('success', 'Pet profile updated successfully!');
    }
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:11',
            'gender' => 'nullable|string',
            'house_no' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'barangay' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'profile_image_base64' => 'nullable|string',
        ]);

        $data = $request->only([
            'name', 'email', 'phone', 'gender',
            'house_no', 'street', 'barangay', 'city', 'province'
        ]);

        if ($request->filled('profile_image_base64')) {
            if ($user->profile_image) Storage::disk('public')->delete($user->profile_image);
            $imageData = $request->input('profile_image_base64');
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
            $imageName = 'profiles/' . uniqid() . '.png';
            Storage::disk('public')->put($imageName, base64_decode($imageData));
            $data['profile_image'] = $imageName;
        } elseif ($request->hasFile('profile_image')) {
            if ($user->profile_image) Storage::disk('public')->delete($user->profile_image);
            $data['profile_image'] = $request->file('profile_image')->store('profiles', 'public');
        }

        $user->update($data);

        return back()->with('success', 'Profile updated successfully!');
    }
}
