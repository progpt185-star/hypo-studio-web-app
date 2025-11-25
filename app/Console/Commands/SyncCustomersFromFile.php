<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Customer;
use App\Models\Order;

class SyncCustomersFromFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * --file=   Path to CSV/XLSX file (relative to project or absolute)
     * --mode=   prune (default) | dry-run
     * --delete-orders= yes|no (default no)
     * --confirm= yes to actually perform destructive action
     *
     * @var string
     */
    protected $signature = 'sync:customers {--file=} {--mode=prune} {--delete-orders=no} {--confirm=no}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync customers from a file: prune customers not present in file (merge mode assumed). Use with care.';

    public function handle()
    {
        $file = $this->option('file');
        $mode = $this->option('mode');
        $deleteOrders = strtolower($this->option('delete-orders')) === 'yes';
        $confirm = strtolower($this->option('confirm')) === 'yes';

        if (empty($file)) {
            $this->error('Please provide --file=path/to/file.xlsx');
            return 1;
        }

        // Resolve path: allow storage/app relative
        if (!file_exists($file)) {
            $candidate = storage_path('app/' . ltrim($file, '\\/'));
            if (file_exists($candidate)) {
                $file = $candidate;
            }
        }

        if (!file_exists($file)) {
            $this->error('File not found: ' . $file);
            return 1;
        }

        $this->info('Reading file: ' . $file);

        try {
            // Use PhpSpreadsheet IOFactory to read a variety of formats
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
            // convert to zero-based numeric index rows with header detection
            if (empty($rows)) {
                $this->error('No data found in file');
                return 1;
            }
            // If rows use letter keys (A,B,C) we will map them using the first row as header
            $first = reset($rows);
            $isAssoc = false;
            foreach ($first as $k => $v) {
                if (!is_int($k)) { $isAssoc = true; break; }
            }
            if ($isAssoc) {
                // headers present in first row
                $headers = array_map(function($h){ return strtolower(trim((string)$h)); }, array_values($first));
                $dataRows = [];
                $rowIndex = 0;
                foreach ($rows as $r) {
                    if ($rowIndex === 0) { $rowIndex++; continue; }
                    $values = array_values($r);
                    $assoc = [];
                    foreach ($headers as $i => $h) {
                        $assoc[$h] = isset($values[$i]) ? $values[$i] : null;
                    }
                    $dataRows[] = $assoc;
                    $rowIndex++;
                }
                $rows = $dataRows;
            } else {
                // numeric keys already, convert each to associative using first row as header
                $headers = array_map(function($h){ return strtolower(trim((string)$h)); }, $rows[0]);
                $dataRows = [];
                for ($i = 1; $i < count($rows); $i++) {
                    $values = array_values($rows[$i]);
                    $assoc = [];
                    foreach ($headers as $j => $h) {
                        $assoc[$h] = isset($values[$j]) ? $values[$j] : null;
                    }
                    $dataRows[] = $assoc;
                }
                $rows = $dataRows;
            }
        } catch (\Exception $e) {
            $this->error('Failed to read file: ' . $e->getMessage());
            return 1;
        }

        // header detection: lower-case keys
        $headers = array_map(function($h){ return strtolower(trim((string)$h)); }, array_keys($rows[0]));

        $emails = [];
        $phones = [];

        foreach ($rows as $r) {
            // support both associative and numeric-keys rows
            if (is_array($r)) {
                // try to find email and phone by checking values
                foreach ($r as $k => $v) {
                    $key = strtolower(trim((string)$k));
                    $val = trim((string)$v);
                    if ($val === '') continue;

                    // heuristics
                    if (stripos($key, 'email') !== false || filter_var($val, FILTER_VALIDATE_EMAIL)) {
                        $emails[] = $val;
                        continue;
                    }
                    if (preg_match('/^(no|hp|phone|tel|telepon|no\.hp)/i', $key) || preg_match('/^[0\+]/', $val)) {
                        $phones[] = $val;
                        continue;
                    }
                }
            }
        }

        $emails = array_values(array_filter(array_unique(array_map('trim', $emails))));
        $phones = array_values(array_filter(array_unique(array_map('trim', $phones))));

        $this->info('Found ' . count($emails) . ' unique emails and ' . count($phones) . ' unique phones in file.');

        // Determine customers to remove: those whose email not in list AND phone not in list
        $query = Customer::query();
        if (count($emails) > 0) {
            $query->whereNotIn('email', $emails);
        }
        if (count($phones) > 0) {
            $query->whereNotIn('phone', $phones);
        }

        // If both emails and phones provided, we require both not in sets.
        // Build list by filtering manually for correctness
        $toCheck = Customer::all();
        $toDelete = $toCheck->filter(function($c) use ($emails, $phones) {
            $e = $c->email ? in_array(trim($c->email), $emails) : false;
            $p = $c->phone ? in_array(trim($c->phone), $phones) : false;
            // keep if matches either email or phone
            return !($e || $p);
        });

        $countDel = $toDelete->count();
        if ($countDel === 0) {
            $this->info('No customers to remove.');
            return 0;
        }

        $this->warn("Customers that would be removed: {$countDel}");
        $this->table(['id','name','email','phone'], $toDelete->map(function($c){ return [$c->id,$c->name,$c->email,$c->phone]; })->toArray());

        if ($mode === 'dry-run') {
            $this->info('Dry-run mode, no changes made.');
            return 0;
        }

        if (!$confirm) {
            $this->warn('This operation is destructive. Re-run with --confirm=yes to actually delete customers.');
            return 1;
        }

        // perform delete in transaction
        DB::beginTransaction();
        try {
            $ids = $toDelete->pluck('id')->toArray();
            if ($deleteOrders) {
                $this->info('Deleting related orders for customers...');
                Order::whereIn('customer_id', $ids)->delete();
            }
            Customer::whereIn('id', $ids)->delete();
            DB::commit();
            $this->info('Deleted ' . count($ids) . ' customers.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to delete customers: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
