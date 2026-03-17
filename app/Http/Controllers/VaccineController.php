<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Pet;
use App\Models\Vaccination;
use App\Models\Appointment;
use App\Models\VaccineInventory;
use Barryvdh\DomPDF\Facade\Pdf;

class VaccineController extends Controller
{
    public function index(Request $request)
    {
        $query = VaccineInventory::query();

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('batch_no', 'like', '%' . $request->search . '%');
        }

        $vaccines = $query->latest()->paginate(10)->appends(['search' => $request->search]);

        //  Detect if we are on the staff page or admin page
        if ($request->is('staff/*')) {
            return view('staff.vaccine-inventory', compact('vaccines'));
        }

        return view('admin.vaccinations', compact('vaccines'));
    }

    public function status(Request $request)
    {
        $query = Pet::notDeceased()->with(['user', 'latestVaccination']);

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('pet_id', 'like', '%' . $request->search . '%');
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

        Vaccination::create([
            'pet_id' => $id,
            'vaccine_name' => $request->vaccine_name,
            'date_administered' => $request->date_administered,
            'next_due_date' => $request->next_due_date,
            'status' => $request->status, // Save the status!
            'batch_no' => $request->batch_no ?? null,
            'remarks' => $request->remarks ?? null,
            'staff_id' => auth()->id(), // Track who did it
        ]);

        // AUTOMATIC INVENTORY SYNC (Atomic Transaction)
        // Find the matching vaccine inventory and decrement it
        $inventory = VaccineInventory::where('name', $request->vaccine_name)->first();
        if ($inventory && $inventory->stock > 0) {
            $inventory->decrement('stock');
        }

        return back()->with('success', 'Vaccination record added successfully!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'stock' => 'required|integer|min:0',
            'expiry_date' => 'required|date',
        ]);

        $vaccine = VaccineInventory::findOrFail($id);

        // Only update stock and expiry for staff level
        $vaccine->update([
            'stock' => $request->stock,
            'expiry_date' => $request->expiry_date,
        ]);

        return redirect()->route('staff.vaccine-inventory')
            ->with('success', 'Vaccine stock updated successfully.');
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
        $status = 'fully_vaccinated'; // Default
        if ($request->next_due_date) {
            $dueDate = \Carbon\Carbon::parse($request->next_due_date);
            $now = \Carbon\Carbon::now();

            if ($now->gt($dueDate)) {
                $status = 'overdue';
            } elseif ($now->diffInDays($dueDate) <= 14) {
                $status = 'due_soon';
            }
        }
        // --- INVENTORY REDUCTION LOGIC ---
        $status = 'fully_vaccinated';
        if ($request->next_due_date) {
            $dueDate = \Carbon\Carbon::parse($request->next_due_date);
            $now = \Carbon\Carbon::now();
            if ($now->gt($dueDate)) {
                $status = 'overdue';
            } elseif ($now->diffInDays($dueDate) <= 30) {
                $status = 'due_soon';
            }
        }

        // --- INVENTORY REDUCTION LOGIC ---
        $inventory = VaccineInventory::where('name', $request->vaccine_name)->first();
        $finalBatchNo = $request->batch_no ?? ($inventory ? $inventory->batch_no : 'N/A');

        if ($inventory) {
            if ($inventory->stock > 0) {
                $inventory->decrement('stock', 1);
            } else {
                return back()->with('error', "Insufficient stock for {$request->vaccine_name}!");
            }
        }

        // Create History Record
        $pet->vaccinations()->create([
            'appointment_id' => $request->appointment_id,
            'vaccine_name' => $request->vaccine_name,
            'date_administered' => $request->date_administered,
            'next_due_date' => $request->next_due_date,
            'status' => $status,
            'batch_no' => $finalBatchNo,
            'remarks' => $request->remarks,
            'admin_id' => auth()->id(),
        ]);

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
    public function ownerHistory()
    {
        // Fetch vaccination records for the logged-in user's pets
        $vaccinations = Vaccination::whereHas('pet', function ($query) {
            $query->where('user_id', auth()->id());
        })
            ->with('pet')
            ->latest('date_administered')
            ->paginate(10);

        return view('pet-owner.vaccination-history', compact('vaccinations'));
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

