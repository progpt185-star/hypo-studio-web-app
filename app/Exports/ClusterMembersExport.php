<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;
use App\Models\Cluster;
use App\Services\RFMService;

class ClusterMembersExport implements FromCollection, WithHeadings
{
    protected $cluster;
    protected $rfmService;

    public function __construct(Cluster $cluster)
    {
        $this->cluster = $cluster;
        $this->rfmService = new RFMService();
    }

    public function collection()
    {
        $rows = collect();
        $members = $this->cluster->clusterMembers()->with(['customer', 'customer.orders'])->get();
        
        foreach ($members as $m) {
            $rfm = $this->rfmService->calculateCustomerRFM($m->customer);
            
            $rows->push([
                'Customer ID' => $m->customer_id,
                'Cluster' => $m->cluster_number ?? '',
                'RFM Score' => $rfm['rfm_score'],
                'Recency (days)' => $rfm['recency'],
                'Recency Score' => $rfm['recency_score'],
                'Frequency' => $rfm['frequency'],
                'Frequency Score' => $rfm['frequency_score'],
                'Monetary' => $rfm['monetary'],
                'Monetary Score' => $rfm['monetary_score']
            ]);
        }
        return $rows;
    }

    public function headings(): array
    {
        return [
            'Customer ID',
            'Cluster',
            'RFM Score',
            'Recency (days)',
            'Recency Score',
            'Frequency',
            'Frequency Score',
            'Monetary',
            'Monetary Score'
        ];
    }
}
