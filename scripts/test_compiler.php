<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\View\Compilers\ComponentTagCompiler;
use Illuminate\Support\Str;

$compiler = new ComponentTagCompiler();

$testComponents = ['mail::layout', 'mail::message', 'mail::'];

foreach ($testComponents as $comp) {
    echo "Testing component: [$comp]\n";
    try {
        $class = $compiler->componentClass($comp);
        echo "RESULT: Success, class/view: [$class]\n";
    } catch (\Throwable $e) {
        echo "RESULT: FAILED! " . $e->getMessage() . "\n";
    }

    echo "Direct Str::startsWith test: " . (Str::startsWith($comp, 'mail::') ? 'TRUE' : 'FALSE') . "\n";
    echo "-------------------\n";
}
