<?php $__env->startSection('title', 'Data Pelanggan - Hypo Studio'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0">
                <i class="fas fa-users"></i> Data Pelanggan
            </h1>
        </div>
        <div class="col-md-6 text-end">
            <div class="d-inline-block">
                <a href="<?php echo e(url('customers/import')); ?>" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-file-import"></i> Import Data Pelanggan
                </a>
                <a href="<?php echo e(route('customers.create')); ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Pelanggan
                </a>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <!-- Search -->
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Cari pelanggan..." value="<?php echo e(request('search')); ?>">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i> Cari
                    </button>
                </div>
            </form>

            <!-- Table -->
            <?php if($customers->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama</th>
                                <th>No. HP</th>
                                <th>Email</th>
                                <th>Alamat</th>
                                <th width="12%">Total Pesanan</th>
                                <th width="15%">Total Pembelian</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e(($customers->currentPage() - 1) * $customers->perPage() + $key + 1); ?></td>
                                    <td>
                        <strong><?php echo e($customer->name); ?></strong>
                    </td>
                                    <td><?php echo e($customer->phone); ?></td>
                                    <td><?php echo e($customer->email ?? '-'); ?></td>
                                    <td><?php echo e($customer->address ?? '-'); ?></td>
                                    <td>
                        <span class="badge bg-info"><?php echo e($customer->total_orders); ?></span>
                    </td>
                                    <td>
                        <strong>Rp <?php echo e(number_format($customer->total_spent, 0, ',', '.')); ?></strong>
                    </td>
                                    <td>
                        <a href="<?php echo e(route('customers.show', ['customer' => $customer->id])); ?>" class="btn btn-sm btn-info" title="Lihat">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="<?php echo e(route('customers.edit', ['customer' => $customer->id])); ?>" class="btn btn-sm btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="<?php echo e(route('customers.destroy', ['customer' => $customer->id])); ?>" method="POST" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-sm btn-danger" 
                                    onclick="return confirm('Yakin ingin menghapus?')" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">
                                        Tidak ada data pelanggan
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    <?php echo e($customers->links('pagination::bootstrap-5')); ?>

                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox" style="font-size: 48px; color: #ccc;"></i>
                    <p class="text-muted mt-3">Belum ada data pelanggan</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\asoy9\OneDrive\Documents\GitHub\hypo-studio-web-app\resources\views/customers/index.blade.php ENDPATH**/ ?>