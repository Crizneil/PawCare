<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

class Tester {
    use \App\Traits\AppointmentValidation;

    public function test() {
        // Find any pet that has an approved Anti-Rabies appointment today or future
        $appt = \App\Models\Appointment::where('service_type', 'Anti-Rabies')
            ->whereNotIn('status', ['completed', 'Done', 'done', 'cancelled', 'rejected'])
            ->first();
            
        if (!$appt) {
            echo "No active setup found. Creating one...\n";
            $pet = \App\Models\Pet::first();
            if ($pet) {
                $appt = \App\Models\Appointment::create([
                    'user_id' => $pet->user_id,
                    'pet_id' => $pet->id,
                    'pet_name' => $pet->name,
                    'species' => $pet->species,
                    'gender' => $pet->gender,
                    'appointment_date' => now()->addDays(1)->toDateString(),
                    'appointment_time' => '10:00:00',
                    'service_type' => 'Anti-Rabies',
                    'status' => 'approved'
                ]);
            } else {
                echo "No pets in DB.\n";
                return;
            }
        }
        
        echo "Testing with Pet ID: " . $appt->pet_id . "\n";
        $error = $this->checkServiceEligibility($appt->pet_id, 'Anti-Rabies', now()->addDays(2)->toDateString());
        
        echo "Validation Result: " . ($error ? $error : "NO ERROR (Allowed)") . "\n";
    }
}

(new Tester())->test();
