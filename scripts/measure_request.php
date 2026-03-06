<?php
if ($argc < 2) {
    echo "Usage: php measure_request.php <url>\n";
    exit(1);
}
$url = $argv[1];
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);
printf("HTTP:%d TTFB:%.3f Total:%.3f\n", $info['http_code'], $info['starttransfer_time'], $info['total_time']);
