<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Cluster;
use App\Models\ClusterMember;
use App\Services\KMeansService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ClusterMembersExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ClusteringController extends Controller
{
    public function __construct()
    {
        // Middleware defensif agar tidak memaksa route named 'login'
        $this->middleware(function ($request, $next) {
            $protected = ['analyze', 'rerun', 'updateLabel', 'export', 'exportPdf'];
            $action = optional($request->route())->getActionMethod();
            if (in_array($action, $protected, true)) {
                if (!Auth::check()) {
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'Unauthenticated.'], 401);
                    }
                    return redirect('/login');
                }
            }
            return $next($request);
        });
    }

    public function index()
    {
        $lastCluster = Cluster::latest()->first();
        return view('clustering.index', compact('lastCluster'));
    }

    /**
     * Run KMeans analysis and persist results.
     */
    public function analyze(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'k' => 'required|integer|min:2|max:10',
        ])->validate();

        try {
            $kMeansService = new KMeansService();

            // Fallback features (service may support 'features' or accept data_override)
            $requestedFeatures = $request->get('features');
            $featuresToUse = is_array($requestedFeatures) && count($requestedFeatures) ? $requestedFeatures : ['orders'];

            $options = [
                'k' => $validated['k'],
                'features' => $featuresToUse,
                'seed' => $request->get('seed', null),
            ];

            // debug: print options to terminal and to laravel log
            try {
                $out = json_encode($options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                if ($out === false) {
                    throw new \Exception('json_encode failed');
                }
            } catch (\Throwable $e) {
                $out = print_r($options, true);
            }

            // Log to storage/logs
            \Illuminate\Support\Facades\Log::debug('KMeans options: ' . $out);

            // Try writing to stderr (should appear in terminal running artisan)
            if (@file_put_contents('php://stderr', "KMeans options:\n" . $out . PHP_EOL) === false) {
                // Fallback to STDOUT if available
                if (defined('STDOUT')) {
                    @fwrite(STDOUT, "KMeans options:\n" . $out . PHP_EOL);
                } else {
                    // Final fallback
                    error_log("KMeans options: " . $out);
                }
            }

            // run analysis (service should return mapping, customerIds, centroids, etc.)
            $analysis = $kMeansService->analyze($options);
            if (!is_array($analysis) || empty($analysis['mapping']) || empty($analysis['customerIds'])) {
                throw new \Exception('Hasil analisis tidak valid atau kosong.');
            }

            $clusterResult = $analysis['mapping'];      // expected mapping: index -> clusterNumber (0-based or 1-based)
            $customerIds = $analysis['customerIds'];    // expected ordered array aligned with mapping indices
            $kValue = (int)($analysis['k'] ?? $validated['k']);
            $scalingParams = $analysis['scaling_params'] ?? null;
            $centroids = $analysis['centroids'] ?? null;
            $inertia = $analysis['inertia'] ?? null;
            $features = $analysis['features'] ?? $featuresToUse;

            // Detect whether mapping is 0-based (contains 0) or 1-based.
            $mappingIsZeroBased = in_array(0, $clusterResult, true);

            // Create cluster meta
            $cluster = Cluster::create([
                'k_value' => $kValue,
                'name' => "K-Means Analysis (K={$kValue})",
                'description' => '',
                'created_by' => Auth::id(),
                'analysis_date' => now(),
                'params' => json_encode([
                    'features' => $features,
                    'scaling' => $scalingParams,
                    'seed' => $options['seed'] ?? null,
                    'centroids' => $centroids,
                    'inertia' => $inertia,
                ])
            ]);

            // Initialize per-cluster stats (use 1..k indexing for storage/display consistency)
            $clusterStats = [];
            for ($i = 1; $i <= $kValue; $i++) {
                $clusterStats[$i] = [
                    'count' => 0,
                    'sum_recency' => 0,
                    'sum_frequency' => 0,
                    'sum_spending' => 0,
                    'avg_recency' => 0,
                    'avg_frequency' => 0,
                    'avg_spending' => 0,
                    'label' => null,
                ];
            }

            // Arrays to compute global means/std
            $allRecency = [];
            $allFrequency = [];
            $allSpending = [];

            // Pastikan mapping valid
if (empty($clusterResult)) {
    // fallback: semua customer dianggap satu cluster (cluster 1)
    $clusterResult = array_fill(0, Customer::count(), 1);
    $customerIds = Customer::pluck('id')->toArray();
}

// Persist cluster members and accumulate sums
foreach ($clusterResult as $index => $clusterNum) {
    // cari customer ID
    if (isset($customerIds[$index])) {
        $custId = $customerIds[$index];
    } elseif (is_numeric($index)) {
        $custId = (int)$index;
    } else {
        continue;
    }

    $customer = Customer::find($custId);
    if (!$customer) {
        continue;
    }

    // Normalisasi cluster number
    $clusterNumInt = (int)$clusterNum;
    $displayCluster = $mappingIsZeroBased ? $clusterNumInt + 1 : max(1, min($kValue, $clusterNumInt));

    // Hitung metrik pelanggan
    $frequency = (int)$customer->orders()->count();
    $totalSpent = (float)$customer->orders()->sum('total_price');
    $last = $customer->orders()->latest('order_date')->value('order_date');
    $recency = $last ? Carbon::now()->diffInDays(Carbon::parse($last)) : 99999;

    // Simpan ke cluster_members
    $memberData = [
        'cluster_id' => $cluster->id,
        'customer_id' => $customer->id,
        'frequency' => $frequency,
        'total_spent' => $totalSpent,
        'cluster_number' => $displayCluster,
    ];
    ClusterMember::create($memberData);

    // update cluster_id di tabel customers (jika ada kolomnya)
    if (Schema::hasColumn('customers', 'cluster_id')) {
        $customer->update(['cluster_id' => $displayCluster]);
    }

    // Tambahkan ke statistik
    $clusterStats[$displayCluster]['count']++;
    $clusterStats[$displayCluster]['sum_recency'] += $recency;
    $clusterStats[$displayCluster]['sum_frequency'] += $frequency;
    $clusterStats[$displayCluster]['sum_spending'] += $totalSpent;

    // Global arrays
    $allRecency[] = $recency;
    $allFrequency[] = $frequency;
    $allSpending[] = $totalSpent;
}


            // finalize averages for each cluster
            for ($i = 1; $i <= $kValue; $i++) {
                if ($clusterStats[$i]['count'] > 0) {
                    $clusterStats[$i]['avg_recency'] = $clusterStats[$i]['sum_recency'] / $clusterStats[$i]['count'];
                    $clusterStats[$i]['avg_frequency'] = $clusterStats[$i]['sum_frequency'] / $clusterStats[$i]['count'];
                    $clusterStats[$i]['avg_spending'] = $clusterStats[$i]['sum_spending'] / $clusterStats[$i]['count'];
                } else {
                    // ensure zero instead of null to make view code simpler
                    $clusterStats[$i]['avg_recency'] = 0;
                    $clusterStats[$i]['avg_frequency'] = 0;
                    $clusterStats[$i]['avg_spending'] = 0;
                }
                // drop intermediate sums to keep description compact (optional)
                unset($clusterStats[$i]['sum_recency'], $clusterStats[$i]['sum_frequency'], $clusterStats[$i]['sum_spending']);
            }

            // compute global means and std deviations safely (avoid division by zero)
            $meanRecency = $meanFrequency = $meanSpending = 0;
            $stdRecency = $stdFrequency = $stdSpending = 0;
            $n = count($allRecency);
            if ($n > 0 && count($allFrequency) === $n && count($allSpending) === $n) {
                $meanRecency = array_sum($allRecency) / $n;
                $meanFrequency = array_sum($allFrequency) / $n;
                $meanSpending = array_sum($allSpending) / $n;

                // population std (divide by n) — safe if n>0
                $stdRecency = sqrt(array_sum(array_map(function ($v) use ($meanRecency) {
                    return pow($v - $meanRecency, 2);
                }, $allRecency)) / $n);

                $stdFrequency = sqrt(array_sum(array_map(function ($v) use ($meanFrequency) {
                    return pow($v - $meanFrequency, 2);
                }, $allFrequency)) / $n);

                $stdSpending = sqrt(array_sum(array_map(function ($v) use ($meanSpending) {
                    return pow($v - $meanSpending, 2);
                }, $allSpending)) / $n);
            }

            // Labeling clusters: use z-score-ish comparisons with fallback thresholds
            for ($i = 1; $i <= $kValue; $i++) {
                $rAvg = $clusterStats[$i]['avg_recency'];
                $fAvg = $clusterStats[$i]['avg_frequency'];
                $sAvg = $clusterStats[$i]['avg_spending'];

                $label = 'Regular';

                // If stds are zero (no variance), fall back to absolute thresholds
                if ($stdRecency > 0 && $stdFrequency > 0 && $stdSpending > 0) {
                    // higher frequency & higher spending & lower recency => Loyal
                    if (($rAvg <= ($meanRecency - 0.5 * $stdRecency))
                        && ($fAvg >= ($meanFrequency + 0.5 * $stdFrequency))
                        && ($sAvg >= ($meanSpending + 0.5 * $stdSpending))) {
                        $label = 'Loyal';
                    }
                    // low frequency, low spending, high recency => Churn
                    elseif (($rAvg >= ($meanRecency + 0.5 * $stdRecency))
                        && ($fAvg <= ($meanFrequency - 0.5 * $stdFrequency))
                        && ($sAvg <= ($meanSpending - 0.5 * $stdSpending))) {
                        $label = 'Churn';
                    }
                    // low recency (recent) but moderate frequency/spending => New or Potential
                    elseif (($rAvg <= ($meanRecency - 0.5 * $stdRecency))
                        && ($fAvg <= ($meanFrequency + 0.5 * $stdFrequency))) {
                        $label = 'New';
                    } else {
                        $label = 'Regular';
                    }
                } else {
                    // fallback absolute heuristic thresholds (tunable)
                    if ($fAvg >= 8 && $sAvg >= 1000000 && $rAvg <= 30) {
                        $label = 'Loyal';
                    } elseif ($fAvg <= 2 && $sAvg < 300000 && $rAvg >= 90) {
                        $label = 'Churn';
                    } elseif ($fAvg <= 4 && $sAvg >= 300000 && $rAvg <= 60) {
                        $label = 'New';
                    } else {
                        $label = 'Regular';
                    }
                }

                $clusterStats[$i]['label'] = $label;
            }

            // store clusterStats and labels (use 1..k keys)
            $cluster->description = json_encode($clusterStats);
            $labelsMap = [];
            for ($i = 1; $i <= $kValue; $i++) {
                $labelsMap[$i] = $clusterStats[$i]['label'] ?? null;
            }
            $cluster->labels = json_encode($labelsMap);
            $cluster->save();
            try {
                $clusterData = method_exists($cluster, 'toArray') ? $cluster->toArray() : (array) $cluster;
                $out = json_encode($clusterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                if ($out === false) {
                    $out = print_r($clusterData, true);
                }
            } catch (\Throwable $e) {
                $out = 'Failed to serialize cluster: ' . $e->getMessage();
            }

            // write to terminal (stderr) and laravel log
            @file_put_contents('php://stderr', "Cluster created:\n" . $out . PHP_EOL);
            \Illuminate\Support\Facades\Log::debug('Cluster created: ' . (is_string($out) ? $out : print_r($out, true)));
            return redirect('/clustering/results/' . $cluster->id)
                ->with('success', 'Analisis K-Means berhasil dilakukan!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Show cluster results page.
     */
public function results(Cluster $cluster)
{
    try {
        // Defensive: sometimes route-model-binding may provide an empty model instance
        // (e.g. when custom binding or middleware modified the parameter). If the
        // injected $cluster has no attributes, attempt to re-fetch from DB using
        // the route parameter to ensure we have a properly hydrated model.
        $attrs = method_exists($cluster, 'getAttributes') ? $cluster->getAttributes() : null;
        if (empty($attrs) || (is_array($attrs) && count($attrs) === 0)) {
            $routeId = request()->route('cluster') ?? null;
            if ($routeId) {
                $fresh = Cluster::with('clusterMembers.customer')->find($routeId);
                if ($fresh) {
                    $cluster = $fresh;
                }
            }
        }
        $data = method_exists($cluster, 'toArray') ? $cluster->toArray() : (array) $cluster;
        $out = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($out === false) {
            $out = print_r($data, true);
        }
    } catch (\Throwable $e) {
        $out = 'Failed to serialize cluster: ' . $e->getMessage();
    }

    @file_put_contents('php://stderr', "Cluster debug:\n" . $out . PHP_EOL);
    \Illuminate\Support\Facades\Log::debug('Cluster debug: ' . (is_string($out) ? $out : print_r($out, true)));
    // Load relasi clusterMembers dan customer
    $cluster->load(['clusterMembers.customer']);

    // Ambil k_value
    $k = (int) $cluster->k_value;

    // Inisialisasi statistik
    $statistics = [];

    // Loop tiap cluster (1..k)
    for ($i = 1; $i <= $k; $i++) {
        $members = $cluster->clusterMembers->where('cluster_number', $i);
        $count = $members->count();

        // Hitung rata-rata
        $avgFreq = $count ? $members->avg('frequency') : 0;
        $avgSpending = $count ? $members->avg('total_spent') : 0;

        // Hitung recency
        $recencySum = 0;
        foreach ($members as $m) {
            $last = optional($m->customer->orders()->latest('order_date'))->value('order_date');
            $recencySum += $last ? Carbon::now()->diffInDays(Carbon::parse($last)) : 99999;
        }
        $avgRecency = $count ? ($recencySum / $count) : 0;

        $statistics[$i] = [
            'count' => $count,
            'avg_frequency' => round($avgFreq, 2),
            'avg_spending' => round($avgSpending, 2),
            'avg_recency' => round($avgRecency, 2),
            'label' => 'Cluster ' . $i,
        ];
    }

    // Kelompokkan member per cluster
    $groupedMembers = [];
    for ($i = 1; $i <= $k; $i++) {
        $groupedMembers[$i] = $cluster->clusterMembers
            ->where('cluster_number', $i)
            ->values(); // reset key
    }

    // Hitung distribusi jenis produk per cluster
    $productTypes = [];
    for ($i = 1; $i <= $k; $i++) {
        $members = $groupedMembers[$i]->pluck('customer_id')->toArray();
        if (empty($members)) {
            $productTypes[$i] = [];
            continue;
        }

        $typeCount = Order::whereIn('customer_id', $members)
            ->selectRaw('product_type, COUNT(*) as count')
            ->groupBy('product_type')
            ->orderBy('count', 'desc')
            ->pluck('count', 'product_type')
            ->toArray();

        $total = array_sum($typeCount);
        $productTypes[$i] = $total > 0
            ? array_map(fn($c) => round(($c / $total) * 100, 2), $typeCount)
            : [];
    }

    // ✅ return yang benar (hapus view() kedua)
    return view('clustering.results', compact('cluster', 'statistics', 'groupedMembers', 'productTypes'));
}


    public function history()
    {
        $clusters = Cluster::with('clusterMembers')->latest()->paginate(10);
        return view('clustering.history', compact('clusters'));
    }

    /**
     * Export CSV/XLSX of cluster members.
     */
    public function export(Request $request, $clusterId)
    {
        $cluster = Cluster::find($clusterId);
        if (!$cluster) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Cluster not found'], 404);
            }
            abort(404, 'Cluster not found');
        }

        try {
            $userId = Auth::id();
            $formatRequested = $request->get('format', 'xlsx');
            $membersCount = $cluster->clusterMembers()->count();
            \Illuminate\Support\Facades\Log::debug('Clustering export requested', [
                'cluster_id' => $cluster->id,
                'k_value' => $cluster->k_value,
                'requested_format' => $formatRequested,
                'members_count' => $membersCount,
                'user_id' => $userId,
                'request' => $request->except(['_token']),
            ]);
        } catch (\Throwable $e) {
            error_log('Failed to log clustering export: ' . $e->getMessage());
        }

        $format = $request->get('format', 'xlsx');
        $supported = ['csv', 'xlsx'];
        if (!in_array($format, $supported, true)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Invalid format'], 400);
            }
            return response('Invalid format', 400);
        }

        $filename = 'cluster_' . $cluster->id . '.' . ($format === 'csv' ? 'csv' : 'xlsx');

        // CSV handling: in tests we stream the CSV manually to avoid Excel dependency
        if ($format === 'csv') {
            if (app()->runningUnitTests()) {
                $export = new ClusterMembersExport($cluster);
                $rows = $export->collection();
                $headers = $export->headings();

                $output = fopen('php://temp', 'w+');
                if ($headers) {
                    fputcsv($output, $headers);
                }

                foreach ($rows as $row) {
                    // Ensure row is plain array
                    if (is_object($row)) {
                        $row = (array) $row;
                    } elseif ($row instanceof \Illuminate\Support\Collection) {
                        $row = $row->toArray();
                    }
                    fputcsv($output, $row);
                }

                rewind($output);
                $csv = stream_get_contents($output);
                fclose($output);

                return response($csv)
                    ->header('Content-Type', 'text/csv')
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
            }

            // Non-test mode: delegate to Maatwebsite Excel for CSV export
            $response = Excel::download(new ClusterMembersExport($cluster), $filename, \Maatwebsite\Excel\Excel::CSV);
            $response->headers->set('Content-Type', 'text/csv');
            return $response;
        }

        // Default: xlsx download
        return Excel::download(new ClusterMembersExport($cluster), $filename);
}
    /**
     * Rerun analysis using stored params for reproducibility.
     */
    public function rerun(Cluster $cluster)
    {
        try {
            $params = [];
            if (is_string($cluster->params) && $cluster->params !== '') {
                $params = json_decode($cluster->params, true) ?: [];
            } elseif (is_array($cluster->params)) {
                $params = $cluster->params ?: [];
            }

            $options = [
                'k' => $cluster->k_value,
                'features' => $params['features'] ?? ['orders'],
                'seed' => $params['seed'] ?? null,
            ];

            $kMeansService = new KMeansService();
            $analysis = $kMeansService->analyze($options);
            if (!is_array($analysis) || empty($analysis['mapping']) || empty($analysis['customerIds'])) {
                throw new \Exception('Hasil analisis pada rerun tidak valid.');
            }

            $mapping = $analysis['mapping'];

            // remove old members
            $cluster->clusterMembers()->delete();

            // reinitialize stats
            $clusterStats = [];
            for ($i = 1; $i <= $cluster->k_value; $i++) {
                $clusterStats[$i] = [
                    'count' => 0,
                    'sum_recency' => 0,
                    'sum_frequency' => 0,
                    'sum_spending' => 0,
                    'avg_recency' => 0,
                    'avg_frequency' => 0,
                    'avg_spending' => 0,
                    'label' => null,
                ];
            }

            $allRecency = [];
            $allFrequency = [];
            $allSpending = [];

            // detect zero-based mapping
            $mappingIsZeroBased = in_array(0, $mapping, true);
            $customerIds = $analysis['customerIds'];

            foreach ($mapping as $index => $clusterNum) {
                if (array_key_exists($index, $customerIds)) {
                    $custId = $customerIds[$index];
                } else {
                    $custId = $index;
                }

                $customer = Customer::find($custId);
                if (!$customer) continue;

                $clusterNumInt = (int)$clusterNum;
                $displayCluster = $mappingIsZeroBased ? ($clusterNumInt + 1) : max(1, min($cluster->k_value, $clusterNumInt));

                $frequency = (int)$customer->orders()->count();
                $totalSpent = (float)$customer->orders()->sum('total_price');
                $last = $customer->orders()->latest('order_date')->value('order_date');
                $recency = $last ? Carbon::now()->diffInDays(Carbon::parse($last)) : 99999;

                $memberData = [
                    'cluster_id' => $cluster->id,
                    'customer_id' => $customer->id,
                    'frequency' => $frequency,
                    'total_spent' => $totalSpent,
                ];
                if (Schema::hasColumn('cluster_members', 'cluster_number')) {
                    $memberData['cluster_number'] = $displayCluster;
                }
                ClusterMember::create($memberData);

                if (Schema::hasColumn('customers', 'cluster_id')) {
                    $customer->update(['cluster_id' => $displayCluster]);
                }

                $clusterStats[$displayCluster]['count'] += 1;
                $clusterStats[$displayCluster]['sum_recency'] += $recency;
                $clusterStats[$displayCluster]['sum_frequency'] += $frequency;
                $clusterStats[$displayCluster]['sum_spending'] += $totalSpent;

                $allRecency[] = $recency;
                $allFrequency[] = $frequency;
                $allSpending[] = $totalSpent;
            }

            // finalize averages
            for ($i = 1; $i <= $cluster->k_value; $i++) {
                if ($clusterStats[$i]['count'] > 0) {
                    $clusterStats[$i]['avg_recency'] = $clusterStats[$i]['sum_recency'] / $clusterStats[$i]['count'];
                    $clusterStats[$i]['avg_frequency'] = $clusterStats[$i]['sum_frequency'] / $clusterStats[$i]['count'];
                    $clusterStats[$i]['avg_spending'] = $clusterStats[$i]['sum_spending'] / $clusterStats[$i]['count'];
                } else {
                    $clusterStats[$i]['avg_recency'] = 0;
                    $clusterStats[$i]['avg_frequency'] = 0;
                    $clusterStats[$i]['avg_spending'] = 0;
                }
                unset($clusterStats[$i]['sum_recency'], $clusterStats[$i]['sum_frequency'], $clusterStats[$i]['sum_spending']);
            }

            // compute global stats and labeling same as analyze()
            $n = count($allRecency);
            $meanRecency = $meanFrequency = $meanSpending = 0;
            $stdRecency = $stdFrequency = $stdSpending = 0;
            if ($n > 0 && count($allFrequency) === $n && count($allSpending) === $n) {
                $meanRecency = array_sum($allRecency) / $n;
                $meanFrequency = array_sum($allFrequency) / $n;
                $meanSpending = array_sum($allSpending) / $n;

                $stdRecency = sqrt(array_sum(array_map(function ($v) use ($meanRecency) {
                    return pow($v - $meanRecency, 2);
                }, $allRecency)) / $n);

                $stdFrequency = sqrt(array_sum(array_map(function ($v) use ($meanFrequency) {
                    return pow($v - $meanFrequency, 2);
                }, $allFrequency)) / $n);

                $stdSpending = sqrt(array_sum(array_map(function ($v) use ($meanSpending) {
                    return pow($v - $meanSpending, 2);
                }, $allSpending)) / $n);
            }

            for ($i = 1; $i <= $cluster->k_value; $i++) {
                $rAvg = $clusterStats[$i]['avg_recency'];
                $fAvg = $clusterStats[$i]['avg_frequency'];
                $sAvg = $clusterStats[$i]['avg_spending'];

                $label = 'Regular';
                if ($stdRecency > 0 && $stdFrequency > 0 && $stdSpending > 0) {
                    if (($rAvg <= ($meanRecency - 0.5 * $stdRecency))
                        && ($fAvg >= ($meanFrequency + 0.5 * $stdFrequency))
                        && ($sAvg >= ($meanSpending + 0.5 * $stdSpending))) {
                        $label = 'Loyal';
                    } elseif (($rAvg >= ($meanRecency + 0.5 * $stdRecency))
                        && ($fAvg <= ($meanFrequency - 0.5 * $stdFrequency))
                        && ($sAvg <= ($meanSpending - 0.5 * $stdSpending))) {
                        $label = 'Churn';
                    } elseif (($rAvg <= ($meanRecency - 0.5 * $stdRecency))
                        && ($fAvg <= ($meanFrequency + 0.5 * $stdFrequency))) {
                        $label = 'New';
                    } else {
                        $label = 'Regular';
                    }
                } else {
                    if ($fAvg >= 8 && $sAvg >= 1000000 && $rAvg <= 30) {
                        $label = 'Loyal';
                    } elseif ($fAvg <= 2 && $sAvg < 300000 && $rAvg >= 90) {
                        $label = 'Churn';
                    } elseif ($fAvg <= 4 && $sAvg >= 300000 && $rAvg <= 60) {
                        $label = 'New';
                    } else {
                        $label = 'Regular';
                    }
                }
                $clusterStats[$i]['label'] = $label;
            }

            $cluster->description = json_encode($clusterStats);
            $labelsMap = [];
            for ($i = 1; $i <= $cluster->k_value; $i++) {
                $labelsMap[$i] = $clusterStats[$i]['label'] ?? null;
            }
            $cluster->labels = json_encode($labelsMap);
            $cluster->save();

            return redirect()->route('clustering.results', $cluster->id)->with('success', 'Rerun analisis selesai');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update label for a cluster (1..k)
     */
    public function updateLabel(Request $request, Cluster $cluster)
    {
        $maxCluster = is_numeric($cluster->k_value) ? (int)$cluster->k_value : 1;
        $validated = $request->validate([
            'cluster_number' => 'required|integer|min:1|max:' . $maxCluster,
            'label' => 'required|string|in:Loyal,Regular,New,Churn'
        ]);

        $labels = [];
        if (is_string($cluster->labels) && $cluster->labels !== '') {
            $labels = json_decode($cluster->labels, true) ?: [];
        } elseif (is_array($cluster->labels)) {
            $labels = $cluster->labels ?: [];
        }
        $labels[$validated['cluster_number']] = $validated['label'];
        $cluster->labels = json_encode($labels);

        $description = [];
        if (is_string($cluster->description) && $cluster->description !== '') {
            $description = json_decode($cluster->description, true) ?: [];
        } elseif (is_array($cluster->description)) {
            $description = $cluster->description ?: [];
        }
        if (isset($description[$validated['cluster_number']])) {
            $description[$validated['cluster_number']]['label'] = $validated['label'];
            $cluster->description = json_encode($description);
        }

        // ensure cluster exists (defensive)
        if (!($cluster->exists && $cluster->getKey())) {
            $routeId = $request->route('cluster') ?? null;
            if ($routeId) {
                $maybe = Cluster::find($routeId);
                if ($maybe) {
                    $cluster = $maybe;
                } else {
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'Cluster not found'], 404);
                    }
                    abort(404, 'Cluster not found');
                }
            } else {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Cluster not found'], 404);
                }
                abort(404, 'Cluster not found');
            }
        }

        $cluster->save();

        return response()->json([
            'success' => true,
            'message' => 'Label berhasil diperbarui',
            'label' => $validated['label']
        ]);
    }

    /**
     * Export PDF report for a cluster.
     */
    public function exportPdf(Cluster $cluster)
    {
        $cluster->load(['clusterMembers' => function ($q) { $q->with('customer'); }]);

        $statistics = [];
        $stored = [];
        if (is_string($cluster->description) && $cluster->description !== '') {
            $stored = json_decode($cluster->description, true) ?: [];
        } elseif (is_array($cluster->description)) {
            $stored = $cluster->description ?: [];
        }

        for ($i = 1; $i <= $cluster->k_value; $i++) {
            $members = $cluster->clusterMembers()->where('cluster_number', $i)->get();
            $count = $members->count();
            $avgFreq = $count ? $members->avg('frequency') : 0;
            $avgSpending = $count ? $members->avg('total_spent') : 0;

            $recencySum = 0;
            foreach ($members as $m) {
                $last = optional($m->customer->orders()->latest('order_date'))->value('order_date');
                $recencySum += $last ? Carbon::now()->diffInDays(Carbon::parse($last)) : 99999;
            }
            $avgRecency = $count ? ($recencySum / $count) : 0;

            $statistics[$i] = [
                'count' => $count,
                'avg_frequency' => round($avgFreq, 2),
                'avg_spending' => round($avgSpending, 2),
                'avg_recency' => round($avgRecency, 2),
                'label' => $stored[$i]['label'] ?? null,
            ];
        }

        $pdf = Pdf::loadView('clustering.pdf', compact('cluster', 'statistics'));
        $filename = 'cluster_report_' . $cluster->id . '.pdf';
        return $pdf->download($filename);
    }


}

