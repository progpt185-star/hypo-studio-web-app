<?php

namespace App\Http\Controllers;

use App\Imports\CustomersImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class CustomerImportController extends Controller
{
    public function show()
    {
        return view('customers.import');
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt,xlsx,xls'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $file = $request->file('file');

        try {
            Excel::import(new CustomersImport(), $file);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import gagal: ' . $e->getMessage());
        }

        return redirect()->route('customers.index')->with('success', 'Import pelanggan selesai.');
    }
}
