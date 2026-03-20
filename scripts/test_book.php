<?php

use Illuminate\Http\Request;
use App\Http\Controllers\PetController;
use App\Models\User;
use App\Models\Pet;
use Illuminate\Support\Facades\Auth;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Mock Auth
$user = User::where('role', 'owner')->first();
if (!$user) {
    echo "No owner user found to test with.\n";
    exit(1);
}
Auth::login($user);

// Mock Pet
$pet = Pet::where('user_id', $user->id)->first();
if (!$pet) {
    echo "No pet found for user {$user->name}.\n";
    exit(1);
}

// Create Request
$request = Request::create('/owner/appointments/book', 'POST', [
    'pet_id' => $pet->id,
    'appointment_date' => now()->addDays(2)->toDateString(),
    'appointment_time' => '09:00 AM', // Test the controller's strtotime fix
    'service_type' => 'vaccination'
]);

// Set the session
$request->setLaravelSession($app['session']->driver());

echo "Attempting to book appointment for Pet: {$pet->name} (ID: {$pet->id})\n";

try {
    $controller = new PetController();
    $response = $controller->book($request);
    echo "Booking call finished.\n";
    if ($response->isRedirect()) {
        echo "Redirected to: " . $response->getTargetUrl() . "\n";
    }
} catch (\Throwable $e) {
    echo "CAUGHT EXCEPTION: " . $e->getMessage() . "\n";
    echo "FILE: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "STACK TRACE:\n" . $e->getTraceAsString() . "\n";
}
