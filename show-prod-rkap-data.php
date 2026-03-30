<?php
// Create a simple data display script

echo "Running: php artisan tinker\n";
echo "Then paste this code:\n\n";

echo 'use Illuminate\Support\Facades\DB;' . "\n";
echo '$conn = DB::connection("dashboard_phinnisi");' . "\n";
echo '$data = $conn->table("prod_rkap_realisasi")->limit(10)->get();' . "\n";
echo 'echo "=== Sample Data ===\n";' . "\n";
echo 'foreach($data as $row) {' . "\n";
echo '    echo "Wilayah: {$row->wilayah} | Periode: {$row->periode} | Jenis: {$row->jenis} | Layanan: {$row->layanan} | Satuan: " . number_format($row->satuan) . "\n";' . "\n";
echo '}' . "\n\n";

echo '$layanan = $conn->table("prod_rkap_realisasi")->distinct()->pluck("layanan");' . "\n";
echo 'echo "\n=== Jenis Layanan Tersedia ===\n";' . "\n";
echo 'foreach($layanan as $l) { echo "- $l\n"; }' . "\n";