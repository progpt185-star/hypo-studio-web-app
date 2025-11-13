<?php

namespace App\Services;

use App\Models\Customer;
use Carbon\Carbon;

class KMeansService
{
    /**
     * Analisis clustering dengan K-Means sederhana berdasarkan data pelanggan.
     */
    public function analyze(array $options)
    {
        $k = $options['k'] ?? 3;
        $features = $options['features'] ?? ['orders'];

        // Ambil semua pelanggan yang punya order
        $customers = Customer::with('orders')->get()->filter(function ($c) {
            return $c->orders->count() > 0;
        });

        if ($customers->count() < $k) {
            throw new \Exception("Jumlah pelanggan (" . $customers->count() . ") kurang dari jumlah cluster (k=$k).");
        }

        // Siapkan dataset RFM (Recency, Frequency, Monetary)
        $data = [];
        $customerIds = [];

        foreach ($customers as $c) {
            $lastOrder = $c->orders->max('order_date');
            $recency = $lastOrder ? Carbon::parse($lastOrder)->diffInDays(Carbon::now()) : 9999;
            $frequency = $c->orders->count();
            $monetary = $c->orders->sum('total_price');

            $data[] = [$recency, $frequency, $monetary];
            $customerIds[] = $c->id;
        }

        // Normalisasi agar skala tiap fitur sama
        $scaled = $this->minMaxNormalize($data);

        // Jalankan algoritma K-Means
        $clusters = $this->kmeans($scaled, $k);

        // Buat mapping customer_id -> cluster_number
        $mapping = [];
        foreach ($clusters['assignments'] as $index => $clusterNum) {
            $mapping[$index] = $clusterNum; // biar controller bisa mapping sesuai urutan customerIds
        }

        return [
            'mapping' => $mapping,          // index → cluster number
            'customerIds' => $customerIds,  // urutan customer
            'centroids' => $clusters['centroids'],
            'features' => $features,
            'k' => $k,
            'inertia' => $clusters['inertia'] ?? null,
        ];
    }

    /**
     * Normalisasi data menggunakan Min-Max scaling
     */
    private function minMaxNormalize(array $data)
    {
        $columns = count($data[0]);
        $mins = array_fill(0, $columns, INF);
        $maxs = array_fill(0, $columns, -INF);

        foreach ($data as $row) {
            for ($i = 0; $i < $columns; $i++) {
                $mins[$i] = min($mins[$i], $row[$i]);
                $maxs[$i] = max($maxs[$i], $row[$i]);
            }
        }

        $normalized = [];
        foreach ($data as $row) {
            $scaled = [];
            for ($i = 0; $i < $columns; $i++) {
                $range = $maxs[$i] - $mins[$i];
                $scaled[$i] = $range > 0 ? ($row[$i] - $mins[$i]) / $range : 0;
            }
            $normalized[] = $scaled;
        }

        return $normalized;
    }

    /**
     * Implementasi K-Means clustering sederhana
     */
    private function kmeans(array $data, int $k, int $maxIterations = 100)
    {
        $numPoints = count($data);
        $numFeatures = count($data[0]);

        // Inisialisasi centroid acak
        $centroids = [];
        $usedIndexes = [];
        while (count($centroids) < $k) {
            $idx = rand(0, $numPoints - 1);
            if (!in_array($idx, $usedIndexes)) {
                $centroids[] = $data[$idx];
                $usedIndexes[] = $idx;
            }
        }

        $assignments = array_fill(0, $numPoints, 0);

        for ($iteration = 0; $iteration < $maxIterations; $iteration++) {
            $changed = false;

            // Step 1: Assign ke centroid terdekat
            for ($i = 0; $i < $numPoints; $i++) {
                $minDist = INF;
                $clusterIndex = 0;

                foreach ($centroids as $ci => $centroid) {
                    $dist = $this->euclideanDistance($data[$i], $centroid);
                    if ($dist < $minDist) {
                        $minDist = $dist;
                        $clusterIndex = $ci;
                    }
                }

                if ($assignments[$i] !== $clusterIndex) {
                    $assignments[$i] = $clusterIndex;
                    $changed = true;
                }
            }

            // Step 2: Update centroid
            $newCentroids = array_fill(0, $k, array_fill(0, $numFeatures, 0));
            $counts = array_fill(0, $k, 0);

            for ($i = 0; $i < $numPoints; $i++) {
                $cluster = $assignments[$i];
                for ($j = 0; $j < $numFeatures; $j++) {
                    $newCentroids[$cluster][$j] += $data[$i][$j];
                }
                $counts[$cluster]++;
            }

            for ($ci = 0; $ci < $k; $ci++) {
                if ($counts[$ci] > 0) {
                    for ($j = 0; $j < $numFeatures; $j++) {
                        $newCentroids[$ci][$j] /= $counts[$ci];
                    }
                } else {
                    // Jika cluster kosong, isi centroid acak lagi
                    $newCentroids[$ci] = $data[rand(0, $numPoints - 1)];
                }
            }

            $centroids = $newCentroids;

            // Berhenti kalau sudah konvergen
            if (!$changed) break;
        }

        return [
            'centroids' => $centroids,
            'assignments' => $assignments
        ];
    }

    /**
     * Hitung jarak Euclidean
     */
    private function euclideanDistance(array $a, array $b)
    {
        $sum = 0;
        for ($i = 0; $i < count($a); $i++) {
            $sum += pow($a[$i] - $b[$i], 2);
        }
        return sqrt($sum);
    }
}
