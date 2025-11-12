<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\KMeansService;

try {
    $kArg = $argv[1] ?? null;
    $kVal = $kArg ? (int)$kArg : 3;
    echo "Starting KMeansService::analyze() with k={$kVal}\n";
    $svc = new KMeansService();
    $options = ['k' => $kVal, 'features' => ['orders'], 'seed' => 42];
    $res = $svc->analyze($options);
    echo "Analysis finished.\n";
    $mapping = $res['mapping'] ?? [];
    echo "Total mapped customers: " . count($mapping) . "\n";
    $counts = [];
    foreach ($mapping as $cid => $cluster) {
        if (!isset($counts[$cluster])) $counts[$cluster] = 0;
        $counts[$cluster]++;
    }
    echo "Cluster distribution: " . json_encode($counts) . "\n";
    echo "Centroids: " . json_encode($res['centroids'] ?? []) . "\n";
    echo "Sample mapping (up to 10):\n";
    $i = 0;
    foreach ($mapping as $cid => $cluster) {
        echo "  $cid => $cluster\n";
        if (++$i >= 10) break;
    }
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
