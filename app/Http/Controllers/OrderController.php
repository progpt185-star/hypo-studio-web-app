<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Gudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\OrdersImport;

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
            'color' => 'nullable|string|max:100',
            'size' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:50',
        ])->validate();

        // If user is admin, attempt to check stock in Gudang
        $user = $request->user();
        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            $gQuery = Gudang::query()->where('product_type', $validated['product_type']);
            if (!empty($validated['color'])) $gQuery->where('color', $validated['color']);
            if (!empty($validated['size'])) $gQuery->where('size', $validated['size']);
            if (!empty($validated['category'])) $gQuery->where('category', $validated['category']);

            $gudang = $gQuery->first();
            if ($gudang) {
                if ($gudang->qty < $validated['quantity']) {
                    return redirect()->back()->withInput()->with('error', 'Stok tidak cukup di gudang. Tersedia: ' . $gudang->qty);
                }
            }
        }

        $order = Order::create($validated);

        // Decrease stock if we found a matching gudang
        if (isset($gudang) && $gudang instanceof Gudang) {
            $gudang->decreaseQty((int) $validated['quantity']);
        }

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
        $file = $request->file('file');
        try {
            Excel::import(new OrdersImport(), $file);
        } catch (\Exception $e) {
            throw $e;
            // return redirect()->route('orders.index')->with('error', 'Import gagal: ' . $e->getMessage());
        }

        return redirect()->route('orders.index')->with('success', 'Data berhasil diimport!');
    }
}
