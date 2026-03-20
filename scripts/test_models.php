<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$m = json_decode(Illuminate\Support\Facades\Http::get('https://generativelanguage.googleapis.com/v1beta/models?key=' . config('services.google_ai.key'))->body(), true)['models'] ?? [];
foreach ($m as $x) {
    if (strpos($x['name'], 'gemini') !== false && in_array('generateContent', $x['supportedGenerationMethods'] ?? [])) {
        echo $x['name'] . "\n";
    }
}
