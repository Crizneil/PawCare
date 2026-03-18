<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$admins = User::where('role', 'admin')->get(['id', 'name', 'email', 'role']);
echo "Admins found: " . $admins->count() . "\n";
foreach ($admins as $admin) {
    echo "- {$admin->name} ({$admin->email})\n";
}

$allRoles = User::distinct()->get(['role']);
echo "\nAll roles in database:\n";
foreach ($allRoles as $r) {
    echo "- {$r->role}\n";
}
