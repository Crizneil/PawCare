<?php
include __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Http\Controllers\PetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$user = User::find(4);
Auth::login($user);

$request = new Request();
$request->merge([
    'start' => '2026-03-01',
    'end' => '2026-03-31'
]);

$controller = new PetController();
$response = $controller->getAvailableSlots($request);
$data = $response->getData(true);

echo "Logged in as User: " . Auth::id() . "\n";
echo "Owner Booked Dates:\n";
print_r($data['owner_booked_dates']);
echo "\nDebug Info:\n";
print_r($data['debug']);
