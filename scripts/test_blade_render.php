<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test simple blade rendering with arrow icons
$html = view('components.test-arrow', [
    'comparisonRealCall' => 10400,
    'comparisonBudgetCall' => 10400,
])->render();

echo $html;
