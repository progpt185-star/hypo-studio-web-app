<?php

namespace App\Imports;

use App\Models\Customer;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CustomersImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Flexible header mapping to support different column names/languages
        // Normalize keys present in $row (heading-row formatting usually lowercases and snake_cases headers)
        $get = function(array $keys) use ($row) {
            foreach ($keys as $k) {
                // try exact key
                if (isset($row[$k]) && $row[$k] !== '') return trim($row[$k]);
                // try lowercase
                $lk = strtolower($k);
                if (isset($row[$lk]) && $row[$lk] !== '') return trim($row[$lk]);
                // try snake_case variant
                $sk = str_replace([' ', '.'], '_', $lk);
                if (isset($row[$sk]) && $row[$sk] !== '') return trim($row[$sk]);
                // try without underscores
                $wk = str_replace(['_', ' ' , '.'], '', $lk);
                if (isset($row[$wk]) && $row[$wk] !== '') return trim($row[$wk]);
            }
            return null;
        };

        $name = $get(['name','nama','nama_pelanggan','nama pelanggan','nama pelanggan ']);
        $email = $get(['email','e-mail','alamat_email']);
        $phone = $get(['phone','no_hp','no.hp','no hp','no_telepon','telepon','nohp','hp','no']);
        $address = $get(['address','alamat','alamat_lengkap','alamat lengkap']);

        // skip rows without minimal data
        if (empty($email) && empty($name) && empty($phone)) {
            return null;
        }

        // Use email as unique key if present
        if (!empty($email)) {
            $customer = Customer::firstOrNew(['email' => $email]);
        } else {
            $customer = new Customer();
            $placeholder = $name ?: 'unknown';
            $customer->email = Str::slug(substr($placeholder,0,50)) . '@example.invalid';
        }

        if (!empty($name)) $customer->name = $name;
        if (!empty($phone)) $customer->phone = $phone;
        if (!empty($address)) $customer->address = $address;

        $customer->save();

        return $customer;
    }
}
