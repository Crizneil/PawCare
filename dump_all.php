<?php
include 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Appointment;

$appts = Appointment::with('user')->get();
foreach ($appts as $a) {
    echo "ID: " . $a->id . ", UserID: " . $a->user_id . ", Email: " . ($a->user ? $a->user->email : 'N/A') . ", Date: " . $a->appointment_date . ", Status: " . $a->status . "\n";
}
