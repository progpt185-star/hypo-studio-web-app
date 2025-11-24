<?php $__env->startSection('title', 'Data Pemesanan - Hypo Studio'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0">
                <i class="fas fa-shopping-cart"></i> Data Pemesanan
            </h1>
        </div>
        <div class="col-md-6 text-end">
            <div class="d-inline-block">
                <a href="<?php echo e(url('orders/import')); ?>" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-file-import"></i> Import Data Pesanan
                </a>
                <a href="<?php echo e(route('orders.create')); ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Pesanan
                </a>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <select class="form-select" name="customer_id">
                        <option value="">Semua Pelanggan</option>
                        <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($customer->id); ?>" 
                                <?php echo e(request('customer_id') == $customer->id ? 'selected' : ''); ?>>
                                <?php echo e($customer->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" name="start_date" 
                           value="<?php echo e(request('start_date')); ?>" placeholder="Tanggal Mulai">
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" name="end_date" 
                           value="<?php echo e(request('end_date')); ?>" placeholder="Tanggal Akhir">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if($orders->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th>Pelanggan</th>
                                <th>Tanggal</th>
                                <th>Produk</th>
                                <th width="8%">Qty</th>
                                <th width="15%">Total Harga</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e(($orders->currentPage() - 1) * $orders->perPage() + $key + 1); ?></td>
                                    <td><strong><?php echo e($order->customer->name); ?></strong></td>
                                    <td><?php echo e($order->order_date->format('d M Y')); ?></td>
                                    <td><?php echo e($order->product_type); ?></td>
                                    <td>
                        <span class="badge bg-info"><?php echo e($order->quantity); ?></span>
                    </td>
                                    <td>
                        <strong>Rp <?php echo e(number_format($order->total_price, 0, ',', '.')); ?></strong>
                    </td>
                                    <td>
                        <a href="<?php echo e(route('orders.edit', ['order' => $order->id])); ?>" class="btn btn-sm btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="<?php echo e(route('orders.destroy', ['order' => $order->id])); ?>" method="POST" class="d-inline">
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
                                        Tidak ada data pesanan
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    <?php echo e($orders->links('pagination::bootstrap-5')); ?>

                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox" style="font-size: 48px; color: #ccc;"></i>
                    <p class="text-muted mt-3">Belum ada data pesanan</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\asoy9\OneDrive\Documents\GitHub\hypo-studio-web-app\resources\views/orders/index.blade.php ENDPATH**/ ?>