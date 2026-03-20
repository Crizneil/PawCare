<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pet;
use App\Models\User;

echo "--- CHECKING USERS ---\n";
$users = User::whereNotNull('profile_image')->get(['id', 'name', 'profile_image']);
foreach($users as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Image: {$u->profile_image}\n";
}

echo "\n--- CHECKING PETS ---\n";
$pets = Pet::whereNotNull('image_url')->get(['id', 'name', 'image_url']);
foreach($pets as $p) {
    echo "ID: {$p->id} | Name: {$p->name} | Image: {$p->image_url}\n";
}
