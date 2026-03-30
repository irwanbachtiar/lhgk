<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\TrafikController;

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
} else {
    echo "Controller returned: ";
    var_dump($response);
}
