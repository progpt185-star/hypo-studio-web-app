<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cluster Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f4f4f4; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Cluster - <?php echo e($cluster->name); ?></h2>
        <p>Tanggal Analisis: <?php echo e(optional($cluster->analysis_date)->format('d M Y H:i') ?? '-'); ?></p>
    </div>

    <h4>Ringkasan per Cluster</h4>
    <?php
        $params = is_string($cluster->params) ? json_decode($cluster->params, true) : ($cluster->params ?? []);
        $features = $params['features'] ?? ['recency','frequency','spending'];
    ?>
    <table>
        <thead>
            <tr>
                <th>Cluster</th>
                <th>Label</th>
                <th>Jumlah</th>
                <?php $__currentLoopData = $features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <th>Avg <?php echo e(ucfirst($f)); ?><?php if($f === 'recency'): ?> (hari)<?php elseif(in_array($f, ['spending'])): ?> (Rp)<?php endif; ?></th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>
        </thead>
        <tbody>
            <?php for($i = 1; $i <= $cluster->k_value; $i++): ?>
                <tr>
                    <td><?php echo e($i); ?></td>
                    <td><?php echo e($statistics[$i]['label'] ?? '-'); ?></td>
                    <td><?php echo e($statistics[$i]['count'] ?? 0); ?></td>
                    <?php $__currentLoopData = $features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $key = "avg_{$f}";
                            $val = $statistics[$i][$key] ?? ($statistics[$i]['averages'][$f] ?? 0);
                        ?>
                        <td>
                            <?php if(is_null($val)): ?>
                                -
                            <?php elseif($f === 'recency'): ?>
                                <?php echo e(number_format($val, 2)); ?>

                            <?php elseif(in_array($f, ['spending'])): ?>
                                Rp <?php echo e(number_format($val, 0, ',', '.')); ?>

                            <?php else: ?>
                                <?php echo e(is_numeric($val) ? number_format($val, 2) : $val); ?>

                            <?php endif; ?>
                        </td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <h4 style="margin-top:20px">Anggota (contoh 50 pertama)</h4>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Cluster</th>
                <th>Frequency</th>
                <th>Total Spent</th>
            </tr>
        </thead>
        <tbody>
            <?php $c=0; ?>
            <?php $__currentLoopData = $cluster->clusterMembers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if ($c++ >= 50) break; ?>
                <tr>
                    <td><?php echo e($c); ?></td>
                    <td><?php echo e($m->customer->name ?? '-'); ?></td>
                    <td><?php echo e($m->cluster_number); ?></td>
                    <td><?php echo e($m->frequency); ?></td>
                    <td>Rp <?php echo e(number_format($m->total_spent,0,',','.')); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH C:\Users\asoy9\OneDrive\Documents\GitHub\hypo-studio-web-app\resources\views/clustering/pdf.blade.php ENDPATH**/ ?>