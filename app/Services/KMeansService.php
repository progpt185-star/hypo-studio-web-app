<?php

namespace App\Services;

use App\Models\Customer;
use Phpml\Clustering\KMeans;

class KMeansService
{
    protected Preprocessor $preprocessor;

    public function __construct()
    {
        $this->preprocessor = new Preprocessor();
    }

    /**
     * Analyze customers and run KMeans clustering.
     * Options:
     *  - k: int (required)
     *  - features: array of attribute/relation names to use as numeric features (required)
     *      Example: ['lifetime_value','orders_count'] or any numeric Customer attribute.
     *  - seed: int|null
     *  - n_init: number of init runs (not used by php-ml KMeans but accepted)
     *
    * Note: This service is feature-driven and does not compute domain-specific features automatically.
    * Callers must supply the desired feature names (e.g. 'orders', 'lifetime_value', 'spending') via the `features` option.
     *
     * Returns array with mapping, centroids, inertia, raw features, params
     */
    public function analyze(array $options): array
    {
        $k = $options['k'] ?? null;
            if (!$k || $k <= 1) {
            throw new \InvalidArgumentException('Parameter k harus diberikan dan > 0');
        }

        $features = $options['features'] ?? [];
        $seed = $options['seed'] ?? null;

        if (empty($features) || !is_array($features)) {
            throw new \InvalidArgumentException('Parameter `features` harus diberikan sebagai array nama atribut/relasi pada model Customer');
        }

        // Ambil data pelanggan (no longer eagerly loading orders by default)
        $customers = Customer::all();
        if ($customers->isEmpty()) {
            throw new \Exception('Data pelanggan tidak ada');
        }

        $raw = [];
        $customerIds = [];

        foreach ($customers as $customer) {
            $featureRow = [];
            foreach ($features as $f) {
                $value = 0.0;
                // If attribute exists and is numeric, use it
                if (array_key_exists($f, $customer->getAttributes())) {
                    $attr = $customer->{$f};
                    if (is_numeric($attr)) {
                        $value = (float) $attr;
                    }
                } else {
                    // If relation is loaded or present, and is countable, use count
                    if ($customer->relationLoaded($f)) {
                        $rel = $customer->{$f};
                        if (is_array($rel) || $rel instanceof \Countable) {
                            $value = (float) count($rel);
                        }
                    } elseif (method_exists($customer, $f)) {
                        // If a relationship method exists, try to get a lightweight count
                        try {
                            $rel = $customer->{$f}();
                            if (method_exists($rel, 'count')) {
                                $value = (float) $rel->count();
                            }
                        } catch (\Throwable $e) {
                            // ignore and leave value as 0.0
                        }
                    }
                }
                $featureRow[] = $value;
            }

            $raw[] = array_combine($features, $featureRow);
            $customerIds[] = $customer->id;
        }

        if (count($raw) < $k) {
            throw new \Exception('Jumlah pelanggan harus lebih besar atau sama dengan k');
        }

        // build numeric matrix
        $matrix = [];
        foreach ($raw as $row) {
            $matrix[] = array_values($row);
        }

        // preprocessing (z-score)
        $prep = $this->preprocessor->fitTransform($matrix);
        $scaled = $prep['matrix'];
        $scalingParams = $prep['params'];

        // run KMeans
        if ($seed !== null) {
            // php-ml KMeans does not accept seed directly; set mt_srand for deterministic init
            mt_srand((int) $seed);
        }

        // run KMeans (use internal deterministic implementation when seed provided)
        // use deterministic internal kmeans with provided seed
        $clusters = $this->runKMeans($scaled, $k, $seed ?? 0);

        // compute centroids from clusters
        $centroids = [];
        foreach ($clusters as $clusterIdx => $clusterPoints) {
            $centroid = [];
            if (empty($clusterPoints)) {
                $centroids[$clusterIdx] = [];
                continue;
            }
            // get first point safely
            $firstPoint = reset($clusterPoints);
            if (!is_array($firstPoint)) {
                $centroids[$clusterIdx] = [];
                continue;
            }
            $cols = count($firstPoint);
            for ($c = 0; $c < $cols; $c++) {
                $sum = 0.0;
                $countPts = 0;
                foreach ($clusterPoints as $pt) {
                    if (isset($pt[$c])) {
                        $sum += $pt[$c];
                        $countPts++;
                    }
                }
                $centroid[$c] = $countPts > 0 ? $sum / $countPts : 0.0;
            }
            $centroids[$clusterIdx] = $centroid;
        }

        // mapping customer id -> cluster number (1..k)
        $mapping = [];
        foreach ($clusters as $clusterIdx => $clusterPoints) {
            foreach ($clusterPoints as $pt) {
                $index = $this->findRowIndex($scaled, $pt);
                if ($index !== null) {
                    $mapping[$customerIds[$index]] = $clusterIdx + 1;
                }
            }
        }

        // compute inertia (sum squared distances to centroid)
        $inertia = 0.0;
        foreach ($clusters as $clusterIdx => $clusterPoints) {
            $centroid = $centroids[$clusterIdx] ?? null;
            if (empty($centroid)) continue;
            foreach ($clusterPoints as $pt) {
                $s = 0.0;
                for ($i = 0; $i < count($pt); $i++) {
                    $s += pow($pt[$i] - $centroid[$i], 2);
                }
                $inertia += $s;
            }
        }

        // prepare centroids in original feature scale (inverse transform)
        $centroids_original = [];
        foreach ($centroids as $cent) {
            if (empty($cent)) {
                $centroids_original[] = [];
                continue;
            }
            $orig = [];
            foreach ($cent as $i => $v) {
                $mean = $scalingParams['means'][$i] ?? 0.0;
                $std = $scalingParams['stds'][$i] ?? 1.0;
                $orig[] = $v * $std + $mean;
            }
            $centroids_original[] = $orig;
        }

        // round centroids for deterministic comparisons
        $round = function ($arr) {
            $res = [];
            foreach ($arr as $i => $row) {
                if (!is_array($row)) { $res[$i] = $row; continue; }
                $resRow = [];
                foreach ($row as $v) {
                    if (is_numeric($v)) {
                        $resRow[] = round($v, 6);
                    } else {
                        $resRow[] = $v;
                    }
                }
                $res[$i] = $resRow;
            }
            return $res;
        };

        $centroids_original = $round($centroids_original);
        $centroids = $round($centroids);

        return [
            'mapping' => $mapping,
            'raw' => $raw,
            'customerIds' => $customerIds,
            'k' => $k,
            'centroids' => $centroids_original,
            'centroids_scaled' => $centroids,
            'inertia' => $inertia,
            'scaling_params' => $scalingParams,
            'features' => $features,
        ];
    }

    protected function findRowIndex(array $matrix, array $row)
    {
        $tol = 1e-6;
        foreach ($matrix as $i => $r) {
            if (!is_array($r) || count($r) !== count($row)) continue;
            $match = true;
            for ($j = 0; $j < count($r); $j++) {
                if (abs((float)$r[$j] - (float)$row[$j]) > $tol) {
                    $match = false;
                    break;
                }
            }
            if ($match) return $i;
        }
        return null;
    }

    /**
     * Simple deterministic KMeans implementation using mt_rand for initialization.
     * Returns array of clusters where each cluster is an array of points.
     */
    protected function runKMeans(array $matrix, int $k, int $seed = 0, int $maxIterations = 100, float $tol = 1e-6): array
    {
        $n = count($matrix);
        if ($n === 0) return array_fill(0, $k, []);
        $dims = count($matrix[0]);
        // initialize deterministic RNG using simple LCG
        $state = (int)$seed;
        $lcg = function() use (&$state) {
            // 32-bit LCG
            $state = (int)((($state * 1664525) + 1013904223) & 0xFFFFFFFF);
            return $state;
        };

        // initialize centroids by picking k unique pseudo-random points
        $indices = [];
        while (count($indices) < $k) {
            $r = $lcg();
            $idx = $n > 1 ? ($r % $n) : 0;
            $indices[$idx] = true;
            if (count($indices) > $n - 1) break; // safety
        }
        $indices = array_keys($indices);
        // if not enough unique picks, fill with first points
        for ($i = count($indices); $i < $k; $i++) {
            $indices[] = $i % $n;
        }

        $centroids = [];
        foreach ($indices as $i) {
            $centroids[] = $matrix[$i];
        }

        $clusters = [];
    for ($iter = 0; $iter < $maxIterations; $iter++) {
            // assign
            $clusters = array_fill(0, $k, []);
            for ($i = 0; $i < $n; $i++) {
                $best = 0;
                $bestDist = null;
                for ($c = 0; $c < $k; $c++) {
                    $d = 0.0;
                    for ($j = 0; $j < $dims; $j++) {
                        $d += pow($matrix[$i][$j] - $centroids[$c][$j], 2);
                    }
                    if ($bestDist === null || $d < $bestDist) {
                        $bestDist = $d;
                        $best = $c;
                    }
                }
                $clusters[$best][] = $matrix[$i];
            }

            // update centroids
            $moved = 0.0;
            for ($c = 0; $c < $k; $c++) {
                if (empty($clusters[$c])) continue;
                $new = array_fill(0, $dims, 0.0);
                foreach ($clusters[$c] as $pt) {
                    for ($j = 0; $j < $dims; $j++) {
                        $new[$j] += $pt[$j];
                    }
                }
                for ($j = 0; $j < $dims; $j++) {
                    $new[$j] /= count($clusters[$c]);
                }
                // compute move
                for ($j = 0; $j < $dims; $j++) {
                    $moved += abs($new[$j] - $centroids[$c][$j]);
                }
                $centroids[$c] = $new;
            }

            if ($moved <= $tol) break;
        }

        return $clusters;
    }
}
