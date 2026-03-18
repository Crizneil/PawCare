<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Appointment;
use App\Models\User;

$appointments = Appointment::where('appointment_date', '2026-03-19')->get();
echo "Appointments on 2026-03-19:\n";
foreach ($appointments as $appt) {
    $user = User::find($appt->user_id);
    echo "- ID: {$appt->id}, User: {$user->name} (ID: {$appt->user_id}), Status: {$appt->status}\n";
}

$allUsers = User::where('role', 'owner')->get(['id', 'name', 'email']);
echo "\nAll Owners:\n";
foreach ($allUsers as $u) {
    echo "- ID: {$u->id}, Name: {$u->name}, Email: {$u->email}\n";
}
