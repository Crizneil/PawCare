<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Appointment;
use App\Models\Vaccination;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminReportController extends Controller
{
    public function appointmentReport(Request $request)
    {
        // 1. Date-Range Filtering Logic
        $range = $request->query('range', 'daily');

        $startDate = match ($range) {
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
            default => now()->startOfDay()
        };
        $endDate = match ($range) {
            'weekly' => now()->endOfWeek(),
            'monthly' => now()->endOfMonth(),
            default => now()->endOfDay()
        };

        // 2. Build the Query with Relationships
        $query = Appointment::with(['user', 'pet'])
            ->whereBetween('appointment_date', [$startDate, $endDate]);

        // 3. Status Summary Logic (Important for your dashboard/report)
        $summary = [
            'total' => (clone $query)->count(),
            'completed' => (clone $query)->whereIn('status', ['completed', 'Done'])->count(),
            'missed' => (clone $query)->where('status', 'missed')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
        ];

        // 4. Get the List
        $appointments = (clone $query)->orderBy('appointment_date', 'asc')->get();

        // 5. Handle PDF Download
        if ($request->has('pdf')) {
            $pdf = Pdf::loadView('admin.reports.appointment_pdf', [
                'data' => $appointments,
                'summary' => $summary,
                'range' => $range,
                'isPdf' => true // This hides the buttons in the final PDF
            ]);

            $pdf->setPaper('a4', 'portrait');
            return $pdf->stream("Appointment_Report_{$range}.pdf");
        }

        // 6. Default Web View
        return view('admin.reports.appointment_pdf', [
            'data' => $appointments,
            'summary' => $summary,
            'range' => $range,
            'isPdf' => false
        ]);
    }

    public function petMedicalHistory($id)
    {
        $pet = \App\Models\Pet::with(['user', 'vaccinations.staff'])->findOrFail($id);
        
        $pdf = Pdf::loadView('admin.reports.pet_medical_history_pdf', [
            'pet' => $pet,
            'vaccinations' => $pet->vaccinations->sortByDesc('date_administered'),
            'clinicName' => 'PawCare Veterinary Clinic',
            'generatedAt' => Carbon::now()->format('M d, Y h:i A')
        ]);

        return $pdf->stream("Medical_History_{$pet->unique_id}.pdf");
    }

    public function activePetsReport(Request $request)
    {
        // 1. Filter Logic
        $species = $request->query('species');
        
        $query = \App\Models\Pet::notDeceased()->where('status', '!=', 'ARCHIVED')->with('user');

        if ($species && in_array($species, ['Dog', 'Cat'])) {
            $query->where('species', $species);
        }

        // 2. Status Summary Logic
        $summary = [
            'total' => (clone $query)->count(),
            'dogs' => (clone $query)->where('species', 'Dog')->count(),
            'cats' => (clone $query)->where('species', 'Cat')->count(),
        ];

        // 3. Get the List
        $pets = $query->orderBy('name', 'asc')->get();

        // 4. Handle PDF Download
        if ($request->has('pdf')) {
            $pdf = Pdf::loadView('admin.reports.active_pets_pdf', [
                'pets' => $pets,
                'summary' => $summary,
                'species' => $species,
                'isPdf' => true
            ]);

            $pdf->setPaper('a4', 'portrait');
            return $pdf->stream("Active_Pets_Report.pdf");
        }

        // 5. Default Web View
        return view('admin.reports.active_pets_pdf', [
            'pets' => $pets,
            'summary' => $summary,
            'species' => $species,
            'isPdf' => false
        ]);
    }
}
