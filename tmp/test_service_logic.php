<?php

use App\Traits\AppointmentValidation;

$tester = new class {
    use AppointmentValidation;
};

$services = [
    'Anti-Rabies' => true,
    '5in1' => true,
    '4-in-1' => true,
    'Deworming' => true,
    'Vaccination' => true,
    'Check-up' => false,
    'Kapon' => false,
    'Medical' => false,
    'Consultation' => false,
];

echo "Testing isVaccinationService:\n";
foreach ($services as $service => $expected) {
    $result = $tester->isVaccinationService($service);
    $status = $result === $expected ? "PASS" : "FAIL";
    echo "[$status] Service: '$service' | Expected: " . ($expected ? 'TRUE' : 'FALSE') . " | Result: " . ($result ? 'TRUE' : 'FALSE') . "\n";
}
