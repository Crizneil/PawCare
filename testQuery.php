<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sql = \App\Models\Appointment::where('id', '!=', null)->toSql();
echo "SQL: " . $sql . "\n";
