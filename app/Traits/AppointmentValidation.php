<?php

namespace App\Traits;

use App\Models\Appointment;
use Carbon\Carbon;

trait AppointmentValidation
{
    /**
     * Map of services to their "Next Due" intervals in days.
     */
    public static $serviceIntervals = [
        'Anti-Rabies' => 365,
        '5in1' => 365,
        '5-in-1' => 365,
        '4in1' => 365,
        '4-in-1' => 365,
        'Deworming' => 90,
    ];

    /**
     * Check if a pet is eligible for a specific service on a proposed date.
     * 
     * @param int $petId
     * @param string $serviceType
     * @param string $proposedDate
     * @return string|null Error message if not eligible, null otherwise.
     */
    public function checkServiceEligibility($petId, $serviceType, $proposedDate)
    {
        $proposedDate = Carbon::parse($proposedDate);
        
        // 1. Check if this is a "Restricted" service with a defined interval
        $intervalDays = null;
        $matchedService = null;

        foreach (self::$serviceIntervals as $key => $days) {
            if (str_contains(strtolower($serviceType), strtolower($key))) {
                $intervalDays = $days;
                $matchedService = $key;
                break;
            }
        }

        if (!$intervalDays) {
            return null; // No restriction for this service type (e.g., Check-up, Kapon)
        }

        // 2. Check for the most recent COMPLETED vaccination record
        $lastShot = \App\Models\Vaccination::where('pet_id', $petId)
            ->where(function($q) use ($matchedService) {
                $q->where('vaccine_name', 'like', "%{$matchedService}%")
                  ->orWhere('remarks', 'like', "%{$matchedService}%");
            })
            ->orderBy('date_administered', 'desc')
            ->first();

        if ($lastShot) {
            $lastDate = Carbon::parse($lastShot->date_administered);
            $nextEligibleDate = $lastDate->copy()->addDays($intervalDays);

            if ($proposedDate->lt($nextEligibleDate)) {
                return "This pet is not yet due for another {$matchedService} shot. " . 
                       "Last shot was on " . $lastDate->format('M d, Y') . ". " .
                       "Please wait until " . $nextEligibleDate->format('M d, Y') . " for the next scheduled dose.";
            }
        }

        // 3. Check for any future APPROVED or PENDING appointments of the same type
        // This prevents double-booking before the first one is even done.
        $futureAppt = Appointment::where('pet_id', $petId)
            ->where('id', '!=', request()->route('id')) // Ignore current record if updating
            ->whereNotIn('status', ['completed', 'Done', 'done', 'cancelled', 'rejected'])
            ->where(function($q) use ($matchedService) {
                $q->where('service_type', 'like', "%{$matchedService}%")
                  ->orWhere('vaccine_name', 'like', "%{$matchedService}%");
            })
            ->where('appointment_date', '>=', now()->toDateString())
            ->first();

        if ($futureAppt) {
            return "This pet already has an active appointment for {$matchedService} on " . 
                   Carbon::parse($futureAppt->appointment_date)->format('M d, Y') . ". " .
                   "Please wait for this to be marked as 'Done' before booking the same service again.";
        }

        return null;
    }

    /**
     * Get the interval in days for a specific service type.
     */
    public function getServiceInterval($serviceType)
    {
        foreach (self::$serviceIntervals as $key => $days) {
            if (str_contains(strtolower($serviceType), strtolower($key))) {
                return $days;
            }
        }
        return 365; // Default to 1 year
    }

    /**
     * Check if a service type is a vaccination or medical recordable service.
     */
    public function isMedicalService($serviceType)
    {
        $medicalServices = ['Vaccination', 'Deworming', 'Check-up', 'check group', 'Kapon', 'Medical'];
        
        // Check exact match from legacy list
        if (in_array($serviceType, $medicalServices)) {
            return true;
        }

        // Check if it contains any of our known vaccine/medical keys
        foreach (self::$serviceIntervals as $key => $days) {
            if (str_contains(strtolower($serviceType), strtolower($key))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Specifically check if a service is a Vaccination or Deworming
     * (Items that should be recorded in the Vaccinations table)
     */
    public function isVaccinationService($serviceType)
    {
        // 1. Explicit check for common terms
        $vaccineKeywords = ['Vaccination', 'Deworming'];
        foreach ($vaccineKeywords as $word) {
            if (str_contains(strtolower($serviceType), strtolower($word))) {
                return true;
            }
        }

        // 2. Check against defined intervals (actual vaccine products)
        foreach (self::$serviceIntervals as $key => $days) {
            if (str_contains(strtolower($serviceType), strtolower($key))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Legacy method for backward compatibility
     */
    protected function checkAntiRabiesEligibility($petId, $proposedDate)
    {
        return $this->checkServiceEligibility($petId, 'Anti-Rabies', $proposedDate);
    }
}
