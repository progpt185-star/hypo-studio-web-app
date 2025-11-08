<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('customer');

        if ($request->has('customer_id') && $request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('order_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('order_date', '<=', $request->end_date);
        }

        $orders = $query->latest('order_date')->paginate(10);
        $customers = Customer::all();

        return view('orders.index', compact('orders', 'customers'));
    }

    public function create()
    {
        $customers = Customer::all();
        return view('orders.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'product_type' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'total_price' => 'required|numeric|min:0',
        ])->validate();

        Order::create($validated);

        return redirect()->route('orders.index')->with('success', 'Pesanan berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $order = Order::findOrFail($id);
        $customers = Customer::all();
        
        return view('orders.edit', [
            'order' => $order,
            'customers' => $customers
        ]);
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        $validated = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'product_type' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'total_price' => 'required|numeric|min:0',
        ])->validate();

        $order->update($validated);

        return redirect()->route('orders.index')->with('success', 'Pesanan berhasil diubah!');
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Pesanan berhasil dihapus!');
    }

    public function import(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv'
        ])->validate();

        // Implementasi import Excel/CSV
        // Menggunakan maatwebsite/excel

        return redirect()->route('orders.index')->with('success', 'Data berhasil diimport!');
    }
}
