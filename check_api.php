<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Simulate pet owner
$user = User::where('role', 'owner')->first();
Auth::login($user);

$start = '2026-03-01'; // Adjust range if needed
$end = '2026-03-31';
$ownerId = auth()->id();

$appointments = Appointment::whereBetween('appointment_date', [$start, $end])
    ->whereNotIn('status', ['cancelled', 'rejected'])
    ->get();

$bookedSlots = [];
$ownerBookedDates = [];

foreach ($appointments as $appt) {
    $date = $appt->appointment_date;
    $status = strtolower($appt->status);

    if ($appt->user_id == $ownerId) { // Use loose comparison just in case
        if (!isset($ownerBookedDates[$date])) {
            $ownerBookedDates[$date] = [];
        }
        $ownerBookedDates[$date][] = [
            'id' => $appt->id,
            'pet_id' => $appt->pet_id,
            'status' => $status
        ];
    }

    if (!in_array($status, ['done', 'completed'])) {
        $bookedSlots[$date][] = date('H:i', strtotime($appt->appointment_time));
    }
}

echo "Owner ID: " . $ownerId . "\n";
echo "Total appointments found in range: " . $appointments->count() . "\n";
echo "Owner booked dates:\n";
print_r($ownerBookedDates);
echo "Booked slots:\n";
print_r($bookedSlots);
