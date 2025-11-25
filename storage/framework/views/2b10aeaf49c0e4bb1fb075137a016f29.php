<?php $__env->startSection('title', 'Dashboard - Hypo Studio'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-0">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </h1>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-0">Total Pelanggan</p>
                            <h2 class="mb-0 text-primary"><?php echo e($totalCustomers); ?></h2>
                        </div>
                        <div class="fs-1 text-primary opacity-50">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-0">Total Pesanan</p>
                            <h2 class="mb-0 text-success"><?php echo e($totalOrders); ?></h2>
                        </div>
                        <div class="fs-1 text-success opacity-50">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-0">Jumlah Cluster</p>
                            <h2 class="mb-0 text-warning"><?php echo e($totalClusters); ?></h2>
                        </div>
                        <div class="fs-1 text-warning opacity-50">
                            <i class="fas fa-network-wired"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-0">Status Sistem</p>
                            <h6 class="mb-0"><span class="badge bg-success">Active</span></h6>
                        </div>
                        <div class="fs-1 text-info opacity-50">
                            <i class="fas fa-server"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Jumlah Pesanan per Bulan
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="orderChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-pie-chart"></i> Pelanggan per Cluster
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="clusterChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-link"></i> Quick Access
                    </h5>
                </div>
                <div class="card-body">
                    <a href="<?php echo e(route('customers.index')); ?>" class="btn btn-outline-primary me-2 mb-2">
                        <i class="fas fa-users"></i> Kelola Pelanggan
                    </a>
                    <a href="<?php echo e(route('orders.index')); ?>" class="btn btn-outline-success me-2 mb-2">
                        <i class="fas fa-shopping-cart"></i> Kelola Pesanan
                    </a>
                    <a href="<?php echo e(url('/clustering')); ?>" class="btn btn-outline-warning me-2 mb-2">
                        <i class="fas fa-network-wired"></i> Analisis K-Means
                    </a>
                    <a href="<?php echo e(url('/clustering/history')); ?>" class="btn btn-outline-info mb-2">
                        <i class="fas fa-history"></i> Riwayat Clustering
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
    }
</style>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script>
    // Orders by Month Chart
    var orderCtx = document.getElementById('orderChart').getContext('2d');
    new Chart(orderCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($ordersByMonth['labels'], 15, 512) ?>,
            datasets: [{
                label: 'Jumlah Pesanan',
                data: <?php echo json_encode($ordersByMonth['data'], 15, 512) ?>,
                backgroundColor: 'rgba(102, 126, 234, 0.5)',
                borderColor: 'rgba(102, 126, 234, 1)',
                borderWidth: 2,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Customers by Cluster Chart
    var clusterCtx = document.getElementById('clusterChart').getContext('2d');
    new Chart(clusterCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($customersByCluster['labels'], 15, 512) ?>,
            datasets: [{
                data: <?php echo json_encode($customersByCluster['data'], 15, 512) ?>,
                backgroundColor: [
                    'rgba(102, 126, 234, 0.8)',
                    'rgba(118, 75, 162, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)'
                ],
                borderColor: [
                    'rgba(102, 126, 234, 1)',
                    'rgba(118, 75, 162, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\asoy9\OneDrive\Documents\GitHub\hypo-studio-web-app\resources\views/dashboard/index.blade.php ENDPATH**/ ?>