<?php
include __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Appointment;

$appts = Appointment::where('user_id', 3)->get();
echo json_encode($appts, JSON_PRETTY_PRINT);
