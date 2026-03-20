<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Simulate Levi (ID 4)
$user = User::find(4);
Auth::login($user);

$start = '2026-03-01';
$end = '2026-03-31';
$ownerId = auth()->id();

$appointments = Appointment::whereBetween('appointment_date', [$start, $end])
    ->whereNotIn('status', ['cancelled', 'rejected'])
    ->get();

$ownerBookedDates = [];
foreach ($appointments as $appt) {
    if ($appt->user_id == $ownerId) {
        $date = $appt->appointment_date;
        if (!isset($ownerBookedDates[$date])) {
            $ownerBookedDates[$date] = [];
        }
        $ownerBookedDates[$date][] = ['id' => $appt->id];
    }
}

echo "Owner ID: " . $ownerId . "\n";
echo "Owner booked dates:\n";
print_r($ownerBookedDates);
