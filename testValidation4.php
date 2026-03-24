<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

class Tester4 {
    use \App\Traits\AppointmentValidation;

    public function test() {
        try {
            $petCount = \App\Models\Pet::withTrashed()->count() + 1;
            $pet = \App\Models\Pet::create([
                'user_id' => null,
                'pet_id' => 'TEST-' . rand(100,999),
                'name' => 'FakerDog',
                'species' => 'Dog',
                'gender' => 'Male',
                'breed' => 'Mixed',
                'birthday' => now(),
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

            $appt = \App\Models\Appointment::create([
                'user_id' => null,
                'pet_id' => $pet->id,
                'pet_name' => $pet->name,
                'species' => $pet->species,
                'gender' => $pet->gender,
                'appointment_date' => now()->addDays(1)->toDateString(),
                'appointment_time' => '10:00:00',
                'service_type' => 'Anti-Rabies',
                'status' => 'approved' 
            ]);

            $error = $this->checkServiceEligibility($pet->id, 'Anti-Rabies', now()->addDays(2)->toDateString());
            file_put_contents('tester_log.txt', "Validation Result: " . ($error ? $error : "NO ERROR (Allowed)"));
            
            $appt->delete();
            $pet->forceDelete();
        } catch (\Throwable $e) {
            file_put_contents('tester_log.txt', "Exception: " . $e->getMessage());
        }
    }
}

(new Tester4())->test();
