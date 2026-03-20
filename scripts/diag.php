<?php
include __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Assuming the user is admin@pawcare.com (ID 1?) or just check all users
$user = User::where('role', 'owner')->first();
if (!$user) {
    echo "No owner user found.\n";
    exit;
}

Auth::login($user);
$ownerId = Auth::id();
echo "Testing as User ID: $ownerId ($user->email)\n";

$today = date('Y-m-d');
$appts = Appointment::where('user_id', $ownerId)->get();

echo "Total appointments for User $ownerId: " . $appts->count() . "\n";
foreach ($appts as $appt) {
    echo "- ID: $appt->id, Date: $appt->appointment_date, Status: $appt->status\n";
}

$api = new \App\Http\Controllers\PetController();
$request = new \Illuminate\Http\Request();
$request->merge(['start' => date('Y-m-01'), 'end' => date('Y-m-31')]);
$response = $api->getAvailableSlots($request);

echo "\nAPI Response for " . date('Y-m') . ":\n";
echo json_encode($response->getData(), JSON_PRETTY_PRINT);
