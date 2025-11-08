<?php

return [
    'app_name' => 'Hypo Studio',
    'version' => '1.0.0',
    'author' => 'Development Team',
    
    'clusters' => [
        'names' => [
            'Pelanggan Loyal',
            'Pelanggan Reguler',
            'Pelanggan Sporadis',
            'Pelanggan Baru',
            'Pelanggan VIP'
        ],
        'descriptions' => [
            'Pelanggan dengan frekuensi pembelian tinggi dan nilai transaksi besar',
            'Pelanggan dengan frekuensi pembelian sedang dan nilai transaksi menengah',
            'Pelanggan dengan frekuensi pembelian rendah',
            'Pelanggan baru yang baru melakukan pembelian',
            'Pelanggan premium dengan nilai transaksi sangat tinggi'
        ]
    ],
    
    'items_per_page' => 10,
    'max_clusters' => 10,
    'min_clusters' => 2,
];
