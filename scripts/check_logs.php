<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ActivityLog;

$logs = ActivityLog::where('action', 'FAILED_LOGIN')
    ->orWhere('description', 'like', '%admin%')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

echo "Recent relevant logs:\n";
foreach ($logs as $log) {
    echo "ID: {$log->id}, Action: {$log->action}, Created: {$log->created_at}, Description: {$log->description}\n";
}
