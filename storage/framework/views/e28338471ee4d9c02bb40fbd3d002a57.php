<?php $__env->startSection('title', 'Hasil Segmentasi - Hypo Studio'); ?>

<?php $__env->startSection('css'); ?>
<style>
    .chart-section {
        transition: opacity 0.3s ease-in-out;
    }
    .chart-section.hidden {
        display: none;
        opacity: 0;
    }
    .chart-section.visible {
        display: block;
        opacity: 1;
    }
    .btn-group .btn-outline-primary.active {
        background-color: #0d6efd;
        color: white;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0">
                <i class="fas fa-chart-pie"></i> Hasil Segmentasi
            </h1>
        </div>
        <div class="col-md-6 text-end d-flex justify-content-end align-items-center">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-outline-primary" data-chart-toggle="statistics">
                    <i class="fas fa-chart-bar"></i> Statistik
                </button>
                <button type="button" class="btn btn-outline-primary" data-chart-toggle="distributions">
                    <i class="fas fa-chart-pie"></i> Distribusi
                </button>
                <button type="button" class="btn btn-outline-primary" data-chart-toggle="products">
                    <i class="fas fa-box"></i> Produk
                </button>
            </div>
            <a href="<?php echo e(url('/clustering')); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12 text-end">
            <a href="<?php echo e(route('clustering.export', ['cluster' => $cluster->id, 'format' => 'xlsx'])); ?>" class="btn btn-success">Export XLSX</a>
            <a href="<?php echo e(route('clustering.export', ['cluster' => $cluster->id, 'format' => 'csv'])); ?>" class="btn btn-outline-secondary">Export CSV</a>
            <a href="<?php echo e(route('clustering.pdf', $cluster->id)); ?>" class="btn btn-outline-dark">Download PDF</a>
            <a href="<?php echo e(route('clustering.rerun', $cluster->id)); ?>" class="btn btn-warning">Rerun Analisis (simpan params)</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-info">
                <strong>Analisis:</strong> <?php echo e($cluster->name); ?> | 
                <strong>Tanggal:</strong> <?php echo e(optional($cluster->analysis_date)->format('d M Y H:i') ?? 'N/A'); ?>

            </div>
        </div>
    </div>

    <!-- Statistics (R/F/M per cluster) -->
    <div class="row mb-4">
        <?php for($i = 1; $i <= $cluster->k_value; $i++): ?>
            <?php
                $label = $statistics[$i]['label'] ?? null;
                $count = $statistics[$i]['count'] ?? 0;
                $avgRecency = $statistics[$i]['avg_recency'] ?? 0;
                $avgFreq = $statistics[$i]['avg_frequency'] ?? 0;
                $avgMon = $statistics[$i]['avg_spending'] ?? 0;
            ?>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">
                            Kelompok <?php echo e($i); ?>

                            <span class="cluster-label" data-cluster="<?php echo e($i); ?>">
                                <?php if($label): ?> - <?php echo e($label); ?> <?php endif; ?>
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-secondary ms-2 edit-label-btn" 
                                    data-cluster="<?php echo e($i); ?>" 
                                    data-current="<?php echo e($label ?? ''); ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                        </h5>
                        <hr>
                        <p class="mb-2"><strong>Jumlah Pelanggan:</strong><br>
                            <span class="badge bg-primary" style="font-size: 14px;"><?php echo e($count); ?></span>
                        </p>
                        <p class="mb-2"><strong>Rata-rata Recency:</strong><br>
                            <?php echo e(number_format($avgRecency, 2)); ?> hari
                        </p>
                        <p class="mb-2"><strong>Rata-rata Frekuensi:</strong><br>
                            <?php echo e(number_format($avgFreq, 2)); ?> transaksi
                        </p>
                        <p class="mb-0"><strong>Rata-rata Spending:</strong><br>
                            Rp <?php echo e(number_format($avgMon, 0, ',', '.')); ?></p>
                    </div>
                </div>
            </div>
        <?php endfor; ?>
    </div>

    <!-- Analysis params -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Parameter Analisis</h5>
                </div>
                <div class="card-body">
                    <?php
                        $params = $cluster->params ? json_decode($cluster->params, true) : [];
                    ?>
                    <p><strong>Fitur:</strong> <?php echo e(implode(', ', $params['features'] ?? ['recency','frequency','monetary'])); ?></p>
                    <p><strong>Seed:</strong> <?php echo e($params['seed'] ?? '-'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Centroids -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Nilai Tengah Kelompok (rata-rata fitur per kelompok)</h5>
                </div>
                <div class="card-body">
                    <?php
                        $centroids = $params['centroids'] ?? [];
                        $featuresList = $params['features'] ?? ['recency','frequency','monetary'];
                    ?>
                    <?php if(!empty($centroids)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Kelompok</th>
                                        <?php $__currentLoopData = $featuresList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <th><?php echo e(ucfirst($f)); ?></th>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $centroids; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $cent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($idx + 1); ?></td>
                                            <?php $__currentLoopData = $cent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <td><?php echo e(is_numeric($val) ? number_format($val, 2) : $val); ?></td>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <canvas id="centroidRadar" height="160"></canvas>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Centroid tidak tersedia.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Cluster Members Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-table"></i> Daftar Pelanggan per Kelompok
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Pelanggan</th>
                                    <th>Cluster</th>
                                    <th>Frekuensi</th>
                                    <th>Total Spending</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $cluster->clusterMembers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php
                                        $clusterNames = ['Pelanggan Loyal', 'Pelanggan Reguler', 'Pelanggan Sporadis', 'Pelanggan Baru', 'Pelanggan VIP'];
                                    ?>
                                    <tr>
                                        <td><?php echo e($key + 1); ?></td>
                                        <td>
                                            <strong><?php echo e($member->customer->name ?? '-'); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">Kelompok <?php echo e($member->cluster_number ?? '-'); ?></span>
                                            <?php if(!empty($statistics[$member->cluster_number]['label'])): ?>
                                                <br><small><?php echo e($statistics[$member->cluster_number]['label']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo e($member->frequency); ?></span>
                                        </td>
                                        <td>
                                            <strong>Rp <?php echo e(number_format($member->total_spent, 0, ',', '.')); ?></strong>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">
                                            Tidak ada data
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-bar-chart"></i> Distribusi Pelanggan per Kelompok
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="clusterDistribution" height="80"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line"></i> Rata-rata Transaksi per Kelompok
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="spendingChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Type Distribution -->
    <div class="row mt-4">
        <?php for($i = 1; $i <= $cluster->k_value; $i++): ?>
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-pie"></i> Distribusi Jenis Produk - Kelompok <?php echo e($i); ?>

                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="productTypeChart<?php echo e($i); ?>" height="80"></canvas>
                    </div>
                </div>
            </div>
        <?php endfor; ?>
    </div>

    <!-- Actions -->
    <div class="row mt-4">
        <div class="col-md-12">
            <a href="<?php echo e(url('/clustering')); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Analisis
            </a>
            <a href="<?php echo e(url('/clustering/history')); ?>" class="btn btn-info">
                <i class="fas fa-history"></i> Riwayat
            </a>
        </div>
    </div>
</div>
    <!-- Label Edit Modal -->
    <div class="modal fade" id="editLabelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ubah Label Kelompok</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editLabelForm">
                        <input type="hidden" name="cluster_number" id="editClusterNumber">
                        <div class="form-group">
                            <label for="editLabel">Label</label>
                            <select class="form-control" id="editLabel" name="label">
                                <option value="Loyal">Loyal</option>
                                <option value="Regular">Regular</option>
                                <option value="New">Baru</option>
                                <option value="Churn">Tidak Aktif</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="saveLabelBtn">Simpan</button>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script
    <?php
        // Guard k_value and prepare chart data to avoid division by zero
        $k = (int) ($cluster->k_value ?? 0);
        if ($k <= 0) {
            $k = 1; // fallback
        }

        // Build arrays from $statistics (passed from controller)
        $labels = [];
        $distributionCounts = [];
        $avgRecencyArr = [];
        $avgFreqArr = [];
        $avgMonArr = [];
        for ($i = 1; $i <= $k; $i++) {
            $labels[] = "Kelompok $i";
            $distributionCounts[] = (int) ($statistics[$i]['count'] ?? 0);
            $avgRecencyArr[] = (float) ($statistics[$i]['avg_recency'] ?? 0);
            $avgFreqArr[] = (float) ($statistics[$i]['avg_frequency'] ?? 0);
            $avgMonArr[] = (float) ($statistics[$i]['avg_spending'] ?? 0);
        }
    ?>
    // Cluster Distribution Chart
    var distributionCtx = document.getElementById('clusterDistribution').getContext('2d');
    new Chart(distributionCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels, 15, 512) ?>,
            datasets: [{
                label: 'Jumlah Pelanggan',
                data: <?php echo json_encode($distributionCounts, 15, 512) ?>,
                backgroundColor: 'rgba(102, 126, 234, 0.5)',
                borderColor: 'rgba(102, 126, 234, 1)',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // R/F/M Combined Chart (multiple axes)
    var rfmCtx = document.getElementById('spendingChart').getContext('2d');
    new Chart(rfmCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($labels, 15, 512) ?>,
            datasets: [
                {

                // Centroid Radar Chart
                (function(){
                    var centroids = <?php echo json_encode($params['centroids'] ?? [], 15, 512) ?>;
                    // Centroid Radar Chart
                    if (centroids && centroids.length) {
                        var datasets = centroids.map(function(c, i){
                            return {
                                label: 'Kelompok ' + (i+1),
                                data: c,
                                fill: true,
                                backgroundColor: 'rgba(' + (50 + i*40 % 200) + ',' + (100 + i*30 % 155) + ',200,0.15)',
                                borderColor: 'rgba(' + (50 + i*40 % 200) + ',' + (100 + i*30 % 155) + ',200,1)'
                            };
                        });

                        var radarCtx = document.getElementById('centroidRadar').getContext('2d');
                        new Chart(radarCtx, {
                            type: 'radar',
                            data: { labels: features.map(function(f){ return f.charAt(0).toUpperCase()+f.slice(1); }), datasets: datasets },
                            options: { responsive: true, maintainAspectRatio: false }
                        });
                    }
                })();
                    label: 'Rata-rata Recency (hari)',
                    data: <?php echo json_encode($avgRecencyArr, 15, 512) ?>,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    yAxisID: 'yRecency',
                    tension: 0.3,
                    fill: false,
                },
                {
                    label: 'Rata-rata Frekuensi (transaksi)',
                    data: <?php echo json_encode($avgFreqArr, 15, 512) ?>,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    yAxisID: 'yFreq',
                    tension: 0.3,
                    fill: false,
                },
                {
                    label: 'Rata-rata Spending (Rp)',
                    data: <?php echo json_encode($avgMonArr, 15, 512) ?>,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    yAxisID: 'yMoney',
                    tension: 0.3,
                    fill: false,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: { mode: 'index', intersect: false },
            stacked: false,
            scales: {
                yRecency: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: { display: true, text: 'Recency (hari)' }
                },
                yFreq: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    title: { display: true, text: 'Frekuensi' }
                },
                yMoney: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    title: { display: true, text: 'Spending (Rp)' },
                    // offset the axis to avoid overlap
                    offset: true
                }
            }
        }
    });

    // Product Type Distribution Charts
    <?php
        $productTypeData = $productTypes ?? [];
    ?>
    <?php for($i = 1; $i <= $k; $i++): ?>
        <?php
            $types = array_keys($productTypeData[$i] ?? []);
            $percentages = array_values($productTypeData[$i] ?? []);
        ?>
        var productTypeCtx<?php echo e($i); ?> = document.getElementById('productTypeChart<?php echo e($i); ?>').getContext('2d');
        new Chart(productTypeCtx<?php echo e($i); ?>, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($types, 15, 512) ?>,
                datasets: [{
                    data: <?php echo json_encode($percentages, 15, 512) ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    title: {
                        display: true,
                        text: 'Persentase Jenis Produk'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.formattedValue || '';
                                return label + ': ' + value + '%';
                            }
                        }
                    }
                }
            }
        });
    <?php endfor; ?>

    // Label editing functionality
    document.addEventListener('DOMContentLoaded', function() {
        const modal = new bootstrap.Modal(document.getElementById('editLabelModal'));
        const editClusterNumber = document.getElementById('editClusterNumber');
        const editLabel = document.getElementById('editLabel');
        const saveLabelBtn = document.getElementById('saveLabelBtn');
        const clusterId = <?php echo e($cluster->id); ?>;

        document.querySelectorAll('.edit-label-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const clusterNum = this.dataset.cluster;
                const currentLabel = this.dataset.current;
                editClusterNumber.value = clusterNum;
                editLabel.value = currentLabel;
                modal.show();
            });
        });

        saveLabelBtn.addEventListener('click', function() {
            const formData = new FormData();
            formData.append('cluster_number', editClusterNumber.value);
            formData.append('label', editLabel.value);
            formData.append('_token', '<?php echo e(csrf_token()); ?>');

            fetch(`/clustering/${clusterId}/update-label`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const labelSpan = document.querySelector(`.cluster-label[data-cluster="${editClusterNumber.value}"]`);
                    if (labelSpan) {
                        labelSpan.innerHTML = ` - ${data.label}`;
                    }
                    const btn = document.querySelector(`.edit-label-btn[data-cluster="${editClusterNumber.value}"]`);
                    if (btn) {
                        btn.dataset.current = data.label;
                    }
                    modal.hide();

                    // Update table labels
                    document.querySelectorAll(`td small[data-cluster="${editClusterNumber.value}"]`).forEach(el => {
                        el.textContent = data.label;
                    });

                    // Show success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible fade show';
                    alert.innerHTML = `
                        Label berhasil diperbarui
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.querySelector('.container-fluid').insertBefore(alert, document.querySelector('.container-fluid').firstChild);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Show error message
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger alert-dismissible fade show';
                alert.innerHTML = `
                    Terjadi kesalahan saat memperbarui label
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('.container-fluid').insertBefore(alert, document.querySelector('.container-fluid').firstChild);
            });
        });
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\hypo-studio\resources\views/clustering/results.blade.php ENDPATH**/ ?>