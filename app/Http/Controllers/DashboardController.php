<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Cluster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCustomers = Customer::count();
        $totalOrders = Order::count();
        $lastCluster = Cluster::latest()->first();
        $totalClusters = $lastCluster ? $lastCluster->k_value : 0;

        // Data untuk grafik pesanan per bulan (12 bulan terakhir)
        $ordersByMonth = $this->getOrdersByMonth();

        // Data untuk diagram lingkaran pelanggan per cluster
        $customersByCluster = $this->getCustomersByCluster();

        return view('dashboard.index', compact(
            'totalCustomers',
            'totalOrders',
            'totalClusters',
            'ordersByMonth',
            'customersByCluster'
        ));
    }

    private function getOrdersByMonth()
    {
        $months = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $months[] = $month->format('M Y');
            
            $count = Order::whereYear('order_date', $month->year)
                          ->whereMonth('order_date', $month->month)
                          ->count();
            $data[] = $count;
        }

        return [
            'labels' => $months,
            'data' => $data
        ];
    }

    private function getCustomersByCluster()
    {
        $clusters = Cluster::with('clusterMembers')->latest()->first();
        
        if (!$clusters) {
            return [
                'labels' => [],
                'data' => []
            ];
        }

        // Ambil nama cluster dengan default
        $clusterNames = [
            'Pelanggan Loyal',
            'Pelanggan Reguler',
            'Pelanggan Sporadis',
            'Pelanggan Baru',
            'Pelanggan VIP'
        ];

        $labels = [];
        $data = [];

        for ($i = 1; $i <= $clusters->k_value; $i++) {
            $labels[] = $clusterNames[$i-1] ?? "Cluster {$i}";
            $count = $clusters->clusterMembers()->count();
            $data[] = $count;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
}
