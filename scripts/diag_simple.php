<?php
include __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Appointment;
use App\Models\User;

$user = User::where('role', 'owner')->first();
$ownerId = $user ? $user->id : 0;

$appts = Appointment::where('user_id', $ownerId)->get();
$data = [
    'owner_id' => $ownerId,
    'owner_email' => $user ? $user->email : 'none',
    'total_appointments' => $appts->count(),
    'appointments' => $appts->map(function($a) {
        return [
            'id' => $a->id,
            'date' => $a->appointment_date,
            'status' => $a->status
        ];
    })
];

header('Content-Type: application/json');
echo json_encode($data, JSON_PRETTY_PRINT);
