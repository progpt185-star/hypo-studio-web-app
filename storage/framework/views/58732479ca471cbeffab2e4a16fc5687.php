

<?php $__env->startSection('title', 'Import Pelanggan'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Import Data Pelanggan</div>

                <div class="card-body">
                    <?php if(session('error')): ?>
                        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
                    <?php endif; ?>

                    <form action="<?php echo e(url('customers/import')); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label for="file" class="form-label">File CSV / XLSX</label>
                            <input type="file" name="file" id="file" class="form-control" accept=".csv,.xlsx,.xls">
                            <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="d-flex justify-content-end">
                            <a href="<?php echo e(route('customers.index')); ?>" class="btn btn-secondary me-2">Batal</a>
                            <button class="btn btn-primary">Upload & Import</button>
                        </div>
                    </form>
                    <hr>
                    <p class="small text-muted">Format kolom yang disarankan: <code>name,email,phone,address</code> — tetapi import juga menerima variasi Bahasa Indonesia seperti <code>Nama Pelanggan, No. HP, Email, Alamat</code>. Baris tanpa email akan dibuat dengan email placeholder.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\asoy9\OneDrive\Documents\GitHub\hypo-studio-web-app\resources\views/customers/import.blade.php ENDPATH**/ ?>