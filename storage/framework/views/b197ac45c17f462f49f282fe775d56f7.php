<?php $__env->startSection('title', 'Analisis K-Means - Hypo Studio'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0">
                <i class="fas fa-network-wired"></i> Analisis K-Means
            </h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?php echo e(url('/clustering/history')); ?>" class="btn btn-info">
                <i class="fas fa-history"></i> Riwayat Clustering
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-cog"></i> Jalankan Analisis
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Masukkan jumlah cluster (k) yang Anda inginkan, kemudian klik tombol untuk memulai analisis 
                        segmentasi pelanggan menggunakan algoritma K-Means.
                    </p>

                    <form action="<?php echo e(url('/clustering/analyze')); ?>" method="POST">
                        <?php echo csrf_field(); ?>

                        <div class="mb-3">
                            <label for="k" class="form-label">Jumlah Cluster (k)</label>
                            <input type="number" class="form-control <?php $__errorArgs = ['k'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="k" name="k" min="2" max="10" value="<?php echo e(old('k', 3)); ?>" required>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle"></i> Masukkan nilai antara 2-10
                            </small>
                            <?php $__errorArgs = ['k'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <button type="submit" class="btn btn-warning w-100">
                            <i class="fas fa-play"></i> Jalankan Analisis
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Informasi
                    </h5>
                </div>
                <div class="card-body">
                    <p><strong>Tentang K-Means Clustering:</strong></p>
                    <p>K-Means adalah algoritma pembelajaran mesin yang mengelompokkan data pelanggan 
                       berdasarkan atribut pembelian mereka (frekuensi dan nilai transaksi) ke dalam k cluster.</p>

                    <p><strong>Interpretasi Hasil:</strong></p>
                    <ul>
                        <li><strong>Cluster 1:</strong> Pelanggan Loyal - Frekuensi & nilai transaksi tinggi</li>
                        <li><strong>Cluster 2:</strong> Pelanggan Reguler - Frekuensi & nilai sedang</li>
                        <li><strong>Cluster 3:</strong> Pelanggan Sporadis - Frekuensi & nilai rendah</li>
                    </ul>

                    <p class="text-muted small mt-3">
                        <i class="fas fa-lightbulb"></i> 
                        Tips: Mulai dengan k=3 untuk hasil yang lebih mudah diinterpretasikan.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php if($lastCluster): ?>
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="fas fa-check-circle"></i> Analisis Terakhir
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Tanggal Analisis:</strong> <?php echo e($lastCluster->analysis_date->format('d M Y H:i')); ?></p>
                                <p><strong>Jumlah Cluster:</strong> <?php echo e($lastCluster->k_value); ?></p>
                                <p><strong>Total Pelanggan:</strong> <?php echo e($lastCluster->clusterMembers()->count()); ?></p>
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="<?php echo e(url('/clustering/results/'.$lastCluster->id)); ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> Lihat Hasil
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\asoy9\OneDrive\Documents\GitHub\hypo-studio-web-app\resources\views/clustering/index.blade.php ENDPATH**/ ?>