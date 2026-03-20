<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$admin = User::where('email', 'admin@pawcare.com')->first();
if ($admin) {
    $admin->password = Hash::make('password123');
    $admin->save();
    echo "Password for admin@pawcare.com has been reset to: password123\n";
} else {
    echo "Admin user not found, creating one...\n";
    User::create([
        'name' => 'Main Admin',
        'email' => 'admin@pawcare.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
        'house_no' => 'Clinic',
        'street' => 'Main Street',
        'barangay' => 'Poblacion',
        'city' => 'City of Meycauayan',
        'province' => 'Bulacan',
    ]);
    echo "Admin user created with password: password123\n";
}
