<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

class Tester3 {
    use \App\Traits\AppointmentValidation;

    public function test() {
        try {
            // 1. Create a brand new fake pet
            $petCount = \App\Models\Pet::withTrashed()->count() + 1;
            $pet = \App\Models\Pet::create([
                'user_id' => null,
                'pet_id' => 'TEST-' . rand(100,999),
                'name' => 'FakerDog',
                'species' => 'Dog',
                'gender' => 'Male',
                'breed' => 'Mixed',
                'owner' => 'Test Owner',
                'owner_phone' => '09123456789',
                'owner_gender' => 'Male',
                'status' => 'ACTIVE',
                'house_no' => '1',
                'street' => 'Street',
                'barangay' => 'Barangay',
                'city' => 'Meycauayan City',
                'province' => 'Bulacan'
            ]);

            // 2. Create one active appointment for it
            $appt = \App\Models\Appointment::create([
                'user_id' => null,
                'pet_id' => $pet->id,
                'pet_name' => $pet->name,
                'species' => $pet->species,
                'gender' => $pet->gender,
                'appointment_date' => now()->addDays(1)->toDateString(),
                'appointment_time' => '10:00:00',
                'service_type' => 'Anti-Rabies',
                'status' => 'approved' // Staff walk-in default
            ]);

            echo "Created Pet ID: {$pet->id} and active Appointment for Anti-Rabies tomorrow.\n";

            // 3. Check service eligibility for the SAME pet and SAME service
            $error = $this->checkServiceEligibility($pet->id, 'Anti-Rabies', now()->addDays(2)->toDateString());
            
            echo "Validation Result for Second Appointment:\n" . ($error ? $error : "NO ERROR (Allowed)") . "\n";
            
            // 4. Cleanup
            $appt->delete();
            $pet->forceDelete();
        } catch (\Throwable $e) {
            echo "Exception caught: " . $e->getMessage() . "\n";
            echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
            echo $e->getTraceAsString();
        }
    }
}

(new Tester3())->test();
