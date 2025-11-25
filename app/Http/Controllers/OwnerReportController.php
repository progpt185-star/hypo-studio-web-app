<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Order;
use App\Models\Customer;
use Carbon\Carbon;

class OwnerReportController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('view-reports')) {
            abort(403, 'Unauthorized');
        }

        // Simple summary stats
        $totalOrders = Order::count();
        $totalRevenue = (float) Order::sum('total_price');

        // Orders in last 30 days grouped by date
        $start = Carbon::now()->subDays(30)->startOfDay();
        $ordersLast30 = Order::where('order_date', '>=', $start)
            ->selectRaw('DATE(order_date) as date, COUNT(*) as count, SUM(total_price) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top products overall
        $topProducts = Order::selectRaw('product_type, COUNT(*) as count, SUM(total_price) as revenue')
            ->groupBy('product_type')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Basic customer counts
        $customerCount = Customer::count();

        return view('owners.report', compact('totalOrders', 'totalRevenue', 'ordersLast30', 'topProducts', 'customerCount'));
    }
}
