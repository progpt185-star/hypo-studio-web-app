<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->has('search')) {
            $query->search($request->search);
        }

        $customers = $query->with('orders')->paginate(10);

        // Tambahkan total pesanan dan pembelian
        $customers->each(function ($customer) {
            $customer->total_orders = $customer->orders->count();
            $customer->total_spent = $customer->orders->sum('total_price');
        });

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers',
            'email' => 'nullable|email|unique:customers',
            'address' => 'required|string',
        ])->validate();

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone,' . $customer->id,
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'address' => 'required|string',
        ])->validate();

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil diubah!');
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil dihapus!');
    }

    public function show($id)
    {
        $customer = Customer::with('orders')->findOrFail($id);
        $customer->total_orders = $customer->orders->count();
        $customer->total_spent = $customer->orders->sum('total_price');

        return view('customers.show', compact('customer'));
    }
}
