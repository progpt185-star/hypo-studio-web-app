<?php $__env->startSection('title', 'Riwayat Clustering - Hypo Studio'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0">
                <i class="fas fa-history"></i> Riwayat Clustering
            </h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?php echo e(url('/clustering')); ?>" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if($clusters->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Analisis</th>
                                <th>Tanggal</th>
                                <th width="8%">K Value</th>
                                <th width="12%">Total Pelanggan</th>
                                <th width="18%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $clusters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $cluster): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e(($clusters->currentPage() - 1) * $clusters->perPage() + $key + 1); ?></td>
                                    <td><strong><?php echo e($cluster->name); ?></strong></td>
                                    <td><?php echo e($cluster->analysis_date->format('d M Y H:i')); ?></td>
                                    <td>
                        <span class="badge bg-primary"><?php echo e($cluster->k_value); ?></span>
                    </td>
                                    <td>
                        <?php echo e($cluster->clusterMembers()->count()); ?>

                    </td>
                                    <td>
                        <a href="<?php echo e(url('/clustering/results/'.$cluster->id)); ?>" class="btn btn-sm btn-info" title="Lihat">
                            <i class="fas fa-eye"></i> Lihat
                        </a>
                        <button class="btn btn-sm btn-danger" onclick="confirm('Yakin ingin menghapus?')" title="Hapus">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">
                                        Belum ada riwayat clustering
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    <?php echo e($clusters->links()); ?>

                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox" style="font-size: 48px; color: #ccc;"></i>
                    <p class="text-muted mt-3">Belum ada riwayat clustering</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\hypo-studio\resources\views/clustering/history.blade.php ENDPATH**/ ?>