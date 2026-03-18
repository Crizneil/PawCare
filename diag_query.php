<?php
include 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Appointment;
use Illuminate\Support\Facades\DB;

$all = Appointment::where('user_id', 4)->get()->toArray();

$start = '2026-03-01';
$end = '2026-03-31';

DB::enableQueryLog();
$filtered = Appointment::whereBetween('appointment_date', [$start, $end])
    ->whereNotIn('status', ['cancelled', 'rejected'])
    ->get()->toArray();

$log = DB::getQueryLog();

$data = [
    'all_for_user_4' => $all,
    'filtered' => $filtered,
    'sql_log' => $log
];

file_put_contents('diag_out.json', json_encode($data, JSON_PRETTY_PRINT));
