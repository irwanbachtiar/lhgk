<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\TrafikController;

// Clear all caches
echo "Clearing caches...\n";
Artisan::call('view:clear');
Artisan::call('cache:clear');

// Check if opcache is enabled and reset it
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared.\n";
}

// Create request simulating user selecting periode and wilayah
$periode = $argv[1] ?? '01-2026';
$wilayah = $argv[2] ?? 'all';

$request = Request::create('/trafik', 'GET', ['periode' => $periode, 'wilayah' => $wilayah]);

$controller = new TrafikController();
$response = $controller->index($request);

if (is_object($response) && method_exists($response, 'render')) {
    $html = $response->render();
    // write to temp file for inspection
    $out = __DIR__ . '/e2e_trafik_output.html';
    file_put_contents($out, $html);
    echo "Rendered view written to: $out\n";
    
    // Check if icons are in the output
    $has_up = strpos($html, 'arrow-up-circle') !== false;
    $has_down = strpos($html, 'arrow-down-circle') !== false;
    
    if ($has_up || $has_down) {
        echo "✓ Icons found in output!\n";
        
        // Show snippet with icon
        if ($has_up) {
            $pos = strpos($html, 'arrow-up-circle');
            echo "arrow-up-circle at position: $pos\n";
            echo "Context: " . substr($html, max(0, $pos - 50), 150) . "\n\n";
        }
        if ($has_down) {
            $pos = strpos($html, 'arrow-down-circle');
            echo "arrow-down-circle at position: $pos\n";
            echo "Context: " . substr($html, max(0, $pos - 50), 150) . "\n";
        }
    } else {
        echo "✗ Icons NOT found in output.\n";
        
        // Show what we have for Call card
        $callPos = strpos($html, '<strong>Call</strong>');
        if ($callPos !== false) {
            echo "\nCall card content:\n";
            echo substr($html, $callPos - 100, 300) . "\n";
        }
    }
} else {
    echo "Controller returned: ";
    var_dump($response);
}
