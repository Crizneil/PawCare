<?php
include 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Appointment;

$appts = Appointment::where('appointment_date', '2026-03-23')->get();
echo "Found " . $appts->count() . " appointments for 2026-03-23\n";
foreach ($appts as $a) {
    echo "ID: " . $a->id . ", User: " . $a->user_id . ", Status: " . $a->status . "\n";
}
