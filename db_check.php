<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

$admin = User::where('role', 'admin')->first();
if ($admin) {
    echo "ID: " . $admin->id . "\n";
    echo "Name: [" . $admin->name . "]\n";
    echo "Email: [" . $admin->email . "]\n";
    echo "Role: [" . $admin->role . "]\n";
    echo "Password Hash: " . ($admin->password ? 'Exists' : 'Empty') . "\n";
    
    // Check for weird characters in email
    echo "Email Hex: " . bin2hex($admin->email) . "\n";
} else {
    echo "No admin found.\n";
}

$users = DB::table('users')->get(['id', 'email', 'role']);
echo "\nAll users in DB:\n";
foreach ($users as $u) {
    echo "ID: {$u->id}, Email: [{$u->email}], Role: [{$u->role}]\n";
}
