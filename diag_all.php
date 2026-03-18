<?php
include 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Appointment;
use App\Models\User;

$appts = Appointment::all();
$data = [
    'total_appointments_in_db' => $appts->count(),
    'appointments' => $appts->map(function($a) {
        return [
            'id' => $a->id,
            'user_id' => $a->user_id,
            'user_email' => $a->user ? $a->user->email : 'N/A',
            'date' => $a->appointment_date,
            'status' => $a->status
        ];
    })
];

header('Content-Type: application/json');
echo json_encode($data, JSON_PRETTY_PRINT);
