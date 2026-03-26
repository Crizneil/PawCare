<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Pet;
use App\Models\Vaccination;
use App\Models\Appointment;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TelegramAlertNotification;

class VaccineController extends Controller
{
    use \App\Traits\NotificationHelper;
    public function status(Request $request)
    {
        $query = Pet::notDeceased()->with(['user', 'latestVaccination']);

        // 1. Search Logic (Fixed to use pet_id instead of unique_id)
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('pet_id', 'like', '%' . $request->search . '%');
            });
        }

        // 2. Filter Logic (Fixed to use the Model Scope)
        if ($request->filled('status')) {
            // This calls the scopeWhereVaccinationStatus function in your Pet model
            $query->whereVaccinationStatus($request->status);
        }

        $pets = $query->latest()->paginate(10);

        return view('admin.vaccination-status', compact('pets'));
    }
    public function store(Request $request, $id)
    {
        $request->validate([
            'vaccine_name' => 'required|string|max:255',
            'date_administered' => 'required|date',
            'next_due_date' => 'nullable|date|after:date_administered',
            'status' => 'required|string',
        ]);

        $vaccination = Vaccination::create([
            'pet_id' => $id,
            'vaccine_name' => $request->vaccine_name,
            'date_administered' => $request->date_administered,
            'next_due_date' => $request->next_due_date,
            'status' => $request->status, // Save the status!
            'batch_no' => $request->batch_no ?? null,
            'remarks' => $request->remarks ?? null,
            'staff_id' => auth()->id(), // Track who did it
        ]);

        // --- NEW: Send Telegram Notification to Clinic Owner ---
        $this->sendTelegramNotification($vaccination, 'vaccination_updated');

        return back()->with('success', 'Vaccination record added successfully!');
    }


    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'vaccine_name' => 'required|string|max:255',
            'date_administered' => 'required|date',
            'next_due_date' => 'nullable|date',
            'batch_no' => 'nullable|string',
            'remarks' => 'nullable|string',
            'appointment_id' => 'nullable|exists:appointments,id'
        ]);

        $pet = Pet::findOrFail($id);

        // --- AUTOMATION LOGIC ---
        $status = 'fully_vaccinated';
        if ($request->next_due_date) {
            $dueDate = Carbon::parse($request->next_due_date);
            $now = Carbon::now();

            if ($now->gt($dueDate)) {
                $status = 'overdue';
            } elseif ($now->diffInDays($dueDate) <= 30) {
                $status = 'due_soon';
            }
        }

        $finalBatchNo = $request->batch_no ?? 'N/A';

        // Create History Record
        $vaccination = $pet->vaccinations()->create([
            'appointment_id' => $request->appointment_id,
            'vaccine_name' => $request->vaccine_name,
            'date_administered' => $request->date_administered,
            'next_due_date' => $request->next_due_date,
            'status' => $status,
            'batch_no' => $finalBatchNo,
            'remarks' => $request->remarks,
            'admin_id' => auth()->id(),
        ]);

        // --- NEW: Send Telegram Notification to Clinic Owner ---
        $this->sendTelegramNotification($vaccination, 'vaccination_updated');

        // ---  UPDATE THE APPOINTMENT RECORD ---
        if ($request->appointment_id) {
            $appointment = Appointment::find($request->appointment_id);
            if ($appointment) {
                $appointment->update([
                    'status' => 'Done',
                    'vaccine_name' => $request->vaccine_name, // This fills the "Vaccine/Treatment" field
                    'batch_no' => $finalBatchNo,
                    'administered_by' => auth()->user()->name,
                    'next_due_date' => $request->next_due_date
                ]);
            }
        }

        return back()->with('success', "Vaccination record for {$pet->name} updated!");
    }
    public function ownerHistory($pet_id = null)
    {
        // Start the query: Get vaccinations for pets belonging to the logged-in user
        $query = Vaccination::whereHas('pet', function ($query) {
            $query->where('user_id', auth()->id());
        });

        // If a specific pet ID is provided in the URL, filter by it
        if ($pet_id) {
            $query->where('pet_id', $pet_id);
        }

        $vaccinations = $query->with('pet')
            ->latest('date_administered')
            ->paginate(10);

        // Optional: You could also fetch the pet name to show a "History for [Pet Name]" title
        $selectedPet = $pet_id ? Pet::find($pet_id) : null;

        return view('pet-owner.vaccination-history', compact('vaccinations', 'selectedPet'));
    }

    public function cancel($id)
    {
        $appointment = Appointment::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($appointment->status === 'pending') {
            $appointment->update(['status' => 'cancelled']);
            return back()->with('success', 'Appointment cancelled successfully.');
        }

        return back()->with('error', 'Only pending appointments can be cancelled.');
    }

}

