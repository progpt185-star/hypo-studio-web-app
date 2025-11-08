<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Collection;

class RFMService
{
    /**
     * Hitung skor RFM untuk satu pelanggan
     */
    public function calculateCustomerRFM(Customer $customer)
    {
        $orders = $customer->orders;
        
        if ($orders->isEmpty()) {
            return [
                'recency' => 0,
                'frequency' => 0,
                'monetary' => 0,
                'recency_score' => 0,
                'frequency_score' => 0,
                'monetary_score' => 0,
                'rfm_score' => 0
            ];
        }

        // Recency: jumlah hari sejak pembelian terakhir
        $lastOrderDate = $orders->max('order_date');
        $recency = Carbon::parse($lastOrderDate)->diffInDays(Carbon::now());
        
        // Frequency: jumlah total pembelian
        $frequency = $orders->count();
        
        // Monetary: total nilai pembelian
        $monetary = $orders->sum('total_amount');

        // Hitung skor (1-5) untuk masing-masing komponen
        $recencyScore = $this->calculateRecencyScore($recency);
        $frequencyScore = $this->calculateFrequencyScore($frequency);
        $monetaryScore = $this->calculateMonetaryScore($monetary);

        // Hitung skor RFM gabungan (range 1-125)
        $rfmScore = $recencyScore * 25 + $frequencyScore * 5 + $monetaryScore;

        return [
            'recency' => $recency,
            'frequency' => $frequency,
            'monetary' => $monetary,
            'recency_score' => $recencyScore,
            'frequency_score' => $frequencyScore,
            'monetary_score' => $monetaryScore,
            'rfm_score' => $rfmScore
        ];
    }

    /**
     * Hitung Recency Score (1-5)
     * Semakin kecil recency (lebih baru), semakin tinggi skornya
     */
    private function calculateRecencyScore(int $recency): int
    {
        if ($recency <= 30) return 5;  // 0-30 hari
        if ($recency <= 60) return 4;  // 31-60 hari
        if ($recency <= 90) return 3;  // 61-90 hari
        if ($recency <= 180) return 2; // 91-180 hari
        return 1;                      // > 180 hari
    }

    /**
     * Hitung Frequency Score (1-5)
     * Semakin banyak frekuensi, semakin tinggi skornya
     */
    private function calculateFrequencyScore(int $frequency): int
    {
        if ($frequency >= 20) return 5;  // >= 20 orders
        if ($frequency >= 10) return 4;  // 10-19 orders
        if ($frequency >= 5) return 3;   // 5-9 orders
        if ($frequency >= 2) return 2;   // 2-4 orders
        return 1;                        // 1 order
    }

    /**
     * Hitung Monetary Score (1-5)
     * Semakin besar total pembelian, semakin tinggi skornya
     */
    private function calculateMonetaryScore(float $monetary): int
    {
        if ($monetary >= 10000000) return 5;  // >= 10jt
        if ($monetary >= 5000000) return 4;   // 5jt-10jt
        if ($monetary >= 2000000) return 3;   // 2jt-5jt
        if ($monetary >= 1000000) return 2;   // 1jt-2jt
        return 1;                             // < 1jt
    }
}