

<?php $__env->startSection('title','Gudang - Daftar Stok'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Manajemen Gudang</h3>
        <?php if(\Illuminate\Support\Facades\Route::has('gudang.create')): ?>
            <a href="<?php echo e(route('gudang.create')); ?>" class="btn btn-primary">Tambah Stok</a>
        <?php endif; ?>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Produk</th>
                            <th>Warna</th>
                            <th>Ukuran</th>
                            <th>Kategori</th>
                            <th>Qty</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($it->id); ?></td>
                            <td><?php echo e($it->product_type); ?></td>
                            <td><?php echo e($it->color); ?></td>
                            <td><?php echo e($it->size); ?></td>
                            <td><?php echo e($it->category); ?></td>
                            <td><?php echo e($it->qty); ?></td>
                            <td>
                                <?php if(\Illuminate\Support\Facades\Route::has('gudang.edit')): ?>
                                    <a href="<?php echo e(route('gudang.edit', $it->id)); ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <?php endif; ?>
                                <?php if(\Illuminate\Support\Facades\Route::has('gudang.destroy')): ?>
                                    <form action="<?php echo e(route('gudang.destroy', $it->id)); ?>" method="post" class="d-inline-block" onsubmit="return confirm('Hapus item?');">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="7" class="text-center">Tidak ada data</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3"><?php echo e($items->links()); ?></div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\asoy9\OneDrive\Documents\GitHub\hypo-studio-web-app\resources\views/gudang/index.blade.php ENDPATH**/ ?>