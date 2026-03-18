<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

$user = User::where('role', 'owner')->first();
Auth::login($user);

$ownerId = auth()->id();
echo "Auth ID: " . $ownerId . " (Type: " . gettype($ownerId) . ")\n";

$appointments = Appointment::where('appointment_date', '2026-03-19')->get();
foreach ($appointments as $appt) {
    echo "Appt ID: " . $appt->id . ", user_id: " . $appt->user_id . " (Type: " . gettype($appt->user_id) . ")\n";
}
