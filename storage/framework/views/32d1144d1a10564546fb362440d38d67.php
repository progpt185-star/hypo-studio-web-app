<?php $__env->startSection('title', 'Detail Pelanggan - Hypo Studio'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="<?php echo e(route('customers.index')); ?>" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <h1 class="h3 mb-0">
                <i class="fas fa-user"></i> Detail Pelanggan: <?php echo e($customer->name); ?>

            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="card-title">Informasi Pelanggan</h5>
                    <hr>
                    <p class="mb-2">
                        <strong>Nama:</strong><br>
                        <?php echo e($customer->name); ?>

                    </p>
                    <p class="mb-2">
                        <strong>No. HP:</strong><br>
                        <?php echo e($customer->phone); ?>

                    </p>
                    <p class="mb-2">
                        <strong>Email:</strong><br>
                        <?php echo e($customer->email ?? '-'); ?>

                    </p>
                    <p class="mb-2">
                        <strong>Alamat:</strong><br>
                        <?php echo e($customer->address); ?>

                    </p>
                    <p class="mb-2">
                        <strong>Terdaftar:</strong><br>
                        <?php echo e(optional($customer->created_at)->format('d M Y H:i') ?? '-'); ?>

                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="card-title">Statistik Pembelian</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <p class="text-muted mb-1">Total Pesanan</p>
                                <h3 class="mb-0 text-primary"><?php echo e($customer->total_orders); ?></h3>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <p class="text-muted mb-1">Total Pembelian</p>
                                <h3 class="mb-0 text-success">Rp <?php echo e(number_format($customer->total_spent, 0, ',', '.')); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Riwayat Pesanan</h5>
                </div>
                <div class="card-body">
                    <?php if($customer->orders->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Produk</th>
                                        <th>Qty</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $customer->orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e(optional($order->order_date)->format('d M Y') ?? '-'); ?></td>
                                            <td><?php echo e($order->product_type); ?></td>
                                            <td><span class="badge bg-info"><?php echo e($order->quantity); ?></span></td>
                                            <td>
                                                <strong>Rp <?php echo e(number_format($order->total_price, 0, ',', '.')); ?></strong>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">
                            Belum ada pesanan
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <a href="<?php echo e(route('customers.edit', ['customer' => $customer->id])); ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <form action="<?php echo e(route('customers.destroy', ['customer' => $customer->id])); ?>" method="POST" class="d-inline">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                    <i class="fas fa-trash"></i> Hapus
                </button>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\asoy9\OneDrive\Documents\GitHub\hypo-studio-web-app\resources\views/customers/show.blade.php ENDPATH**/ ?>