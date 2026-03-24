<?php
use App\Models\Pet;
use App\Models\User;
use App\Models\Appointment;
use App\Traits\AppointmentValidation;
use Carbon\Carbon;

class MockValidator {
    use AppointmentValidation;
}

// 1. Setup - Use an existing owner and pet if possible, or create temp ones
$owner = User::where('role', 'owner')->first();
if (!$owner) {
    echo "No owner found to test with.\n";
    exit;
}

$pet = Pet::where('user_id', $owner->id)->first();
if (!$pet) {
    echo "No pet found for owner {$owner->name}. Creating one...\n";
    $pet = Pet::create([
        'user_id' => $owner->id,
        'name' => 'TestPet',
        'species' => 'Dog',
        'breed' => 'Aspin',
        'gender' => 'Male',
        'status' => 'ACTIVE'
    ]);
}

echo "Testing with Pet: {$pet->name} (ID: {$pet->id}) for Owner: {$owner->name}\n";

$validator = new MockValidator();

// 2. Clean up any existing test appointments for today to start fresh
Appointment::where('pet_id', $pet->id)
    ->where('appointment_date', '>=', now()->toDateString())
    ->delete();

echo "Step 1: Checking eligibility for Anti-Rabies (should be NULL/Allowed)...\n";
$error1 = $validator->checkServiceEligibility($pet->id, 'Anti-Rabies', now()->toDateString());
var_dump($error1);

echo "\nStep 2: Creating a 'fake' pending appointment for Anti-Rabies today...\n";
Appointment::create([
    'user_id' => $owner->id,
    'pet_id' => $pet->id,
    'pet_name' => $pet->name,
    'species' => $pet->species,
    'service_type' => 'Anti-Rabies',
    'appointment_date' => now()->toDateString(),
    'appointment_time' => '08:00',
    'status' => 'approved'
]);

echo "Step 3: Checking eligibility for Anti-Rabies AGAIN (should be BLOCKED)...\n";
$error2 = $validator->checkServiceEligibility($pet->id, 'Anti-Rabies', now()->toDateString());
var_dump($error2);

echo "\nStep 4: Checking eligibility for 5in1 (should be NULL/Allowed - different service)...\n";
$error3 = $validator->checkServiceEligibility($pet->id, '5in1', now()->toDateString());
var_dump($error3);

echo "\nStep 5: Testing '5-in-1' string match (haystack: 5in1, needle: 5-in-1 matches?)...\n";
$error4 = $validator->checkServiceEligibility($pet->id, '5-in-1', now()->toDateString());
var_dump($error4);
