<?php

namespace App\Http\Controllers;

use App\Models\Gudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GudangController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $items = Gudang::orderBy('id', 'desc')->paginate(20);
        return view('gudang.index', compact('items'));
    }

    public function create()
    {
        return view('gudang.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_type' => 'required|string|max:255',
            'color' => 'nullable|string|max:100',
            'size' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'qty' => 'required|integer|min:0'
        ]);

        $item = Gudang::create($data + ['created_by' => Auth::id()]);
        return redirect()->route('gudang.index')->with('success', 'Gudang item created');
    }

    public function edit(Gudang $gudang)
    {
        return view('gudang.edit', ['item' => $gudang]);
    }

    public function update(Request $request, Gudang $gudang)
    {
        $data = $request->validate([
            'product_type' => 'required|string|max:255',
            'color' => 'nullable|string|max:100',
            'size' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'qty' => 'required|integer|min:0'
        ]);
        $gudang->update($data);
        return redirect()->route('gudang.index')->with('success', 'Gudang item updated');
    }

    public function destroy(Gudang $gudang)
    {
        $gudang->delete();
        return redirect()->route('gudang.index')->with('success', 'Gudang item deleted');
    }
}
