<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\Cluster;

class ClusterMembersExport implements FromCollection, WithHeadings
{
    protected $cluster;

    public function __construct(Cluster $cluster)
    {
        $this->cluster = $cluster;
    }

    public function collection()
    {
        $members = $this->cluster->clusterMembers()->with('customer')->get();

        return $members->map(function ($m) {
            return [
                $m->customer_id,
                $m->cluster_number ?? '',
                $m->frequency,
                $m->total_spent,
                optional($m->customer)->name ?? ''
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Customer ID',
            'Cluster',
            'Frequency',
            'Total Spent',
            'Customer Name'
        ];
    }
}
