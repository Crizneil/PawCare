<?php

namespace App\Traits;

use App\Models\Appointment;
use Carbon\Carbon;

trait AppointmentValidation
{
    /**
     * Check if the pet is eligible for an Anti-Rabies appointment.
     * 
     * @param int $petId
     * @param string $proposedDate
     * @return string|null Error message if not eligible, null otherwise.
     */
    protected function checkAntiRabiesEligibility($petId, $proposedDate)
    {
        // Find the most recent completed Anti-Rabies appointment within the last year
        $lastRabies = Appointment::where('pet_id', $petId)
            ->whereIn('status', ['completed', 'Done'])
            ->where(function ($q) {
                $q->where('service_type', 'Anti-Rabies')
                    ->orWhere('vaccine_name', 'like', '%Rabies%');
            })
            ->where('appointment_date', '>', Carbon::parse($proposedDate)->subYear())
            ->orderBy('appointment_date', 'desc')
            ->first();

        if ($lastRabies) {
            $nextEligibleDate = Carbon::parse($lastRabies->appointment_date)->addYear()->format('M d, Y');
            return "This pet has already received an Anti-Rabies vaccine on " . 
                   Carbon::parse($lastRabies->appointment_date)->format('M d, Y') . 
                   ". The next eligible date for an Anti-Rabies booster is " . $nextEligibleDate . ".";
        }

        return null;
    }
}
