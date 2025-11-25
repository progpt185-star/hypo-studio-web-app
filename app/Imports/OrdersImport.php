<?php

namespace App\Imports;

use App\Models\Order;
use App\Models\Customer;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Carbon\Carbon;

class OrdersImport implements ToModel, WithHeadingRow, WithCalculatedFormulas
{
    /**
     * Convert a row to an Order model
     * expected headings (flexible): customer_email OR nama (customer name), order_date/tanggal, product_type/Jenis Bahan,
     * quantity/Jumlah, total_price/total harga, price/Harga
     */
    public function model(array $row)
    {
        // helper to fetch first available key variant from row
        $get = function(array $keys) use ($row) {
            foreach ($keys as $k) {
                if (isset($row[$k]) && $row[$k] !== '') return trim($row[$k]);
                $lk = strtolower($k);
                if (isset($row[$lk]) && $row[$lk] !== '') return trim($row[$lk]);
                $sk = str_replace([' ', '.'], '_', $lk);
                if (isset($row[$sk]) && $row[$sk] !== '') return trim($row[$sk]);
                $wk = str_replace(['_', ' ' , '.'], '', $lk);
                if (isset($row[$wk]) && $row[$wk] !== '') return trim($row[$wk]);
            }
            return null;
        };

        $customerEmail = $get(['customer_email','email','e-mail','alamat_email']);
        $customerName = $get(['nama','customer_name','nama_pelanggan','name']);
        $orderDateRaw = $get(['tanggal','order_date','date']);
        $productType = $get(['Jenis Bahan','jenis bahan','jenis_bahan','product_type','product']);
        $quantityRaw = $get(['Jumlah','quantity','jumlah','qty']);
        $priceRaw = $get(['total harga','total_price','total_harga','total','Harga','harga','price']);

        // If no customer identifier, skip row
        if (empty($customerEmail) && empty($customerName)) {
            return null;
        }

        // Find existing customer by email or by exact name, otherwise create
        if (!empty($customerEmail)) {
            $customer = Customer::firstOrNew(['email' => $customerEmail]);
            if (empty($customer->name) && !empty($customerName)) $customer->name = $customerName;
        } else {
            // try find by name
            $customer = Customer::where('name', $customerName)->first();
            if (!$customer) {
                $customer = new Customer();
                $customer->name = $customerName;
                $customer->email = Str::slug(substr($customerName,0,50)) . '@example.invalid';
            }
        }
        $customer->save();

        // parse date
        $orderDate = null;
        if (!empty($orderDateRaw)) {
            try {
                $orderDate = Carbon::parse($orderDateRaw)->toDateString();
            } catch (\Exception $e) {
                $orderDate = null;
            }
        }

        // parse quantity
        $quantity = 1;
        if (!empty($quantityRaw)) {
            $quantity = (int) filter_var(str_replace(',', '', $quantityRaw), FILTER_SANITIZE_NUMBER_INT);
            if ($quantity <= 0) $quantity = 1;
        }

        // parse price string like 'Rp 1.716.000' or '1.716.000' or '1716000'
        $totalPrice = 0.0;
        if (!empty($priceRaw)) {
            // Hilangkan semua karakter non-digit kecuali titik/koma
            $p = preg_replace('/[^0-9.,]/', '', $priceRaw);
            // Jika ada titik lebih dari satu, asumsikan titik ribuan, hapus semua titik
            if (substr_count($p, '.') > 1) {
                $p = str_replace('.', '', $p);
            }
            // Ganti koma dengan titik (jika ada desimal)
            $p = str_replace(',', '.', $p);
            $totalPrice = (float) $p;
        }
        //throw new \Exception('Debug: customer=' . $customer->id . ', date=' . $orderDate . ', product=' . $productType . ', qty=' . $quantity . ', price=' . $totalPrice . "priceRaw=". $priceRaw);
        return new Order([
            'customer_id' => $customer->id,
            'order_date' => $orderDate ?? now()->toDateString(),
            'product_type' => $productType ?? 'Unknown',
            'quantity' => $quantity,
            'total_price' => $totalPrice,
        ]);
    }
}
