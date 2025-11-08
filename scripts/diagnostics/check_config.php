<?php
function check_config($filename) {
    try {
        $config = include __DIR__ . '/../../config/' . $filename;
        echo $filename . ': ' . gettype($config);
        if (is_array($config)) {
            echo ' (keys: ' . implode(', ', array_keys($config)) . ')';
        } else {
            echo ' = ' . var_export($config, true);
        }
        echo "\n";
    } catch (Throwable $e) {
        echo $filename . ': ERROR - ' . $e->getMessage() . "\n";
    }
}

$configs = [
    'app.php',
    'auth.php',
    'cache.php',
    'database.php',
    'hashing.php',
    'hypo.php',
    'logging.php',
    'queue.php',
    'session.php',
    'view.php'
];

foreach ($configs as $config) {
    check_config($config);
}