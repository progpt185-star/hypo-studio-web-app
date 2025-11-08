<?php

$configDir = __DIR__ . '/../config';
$files = glob($configDir . '/*.php');
foreach ($files as $file) {
    $name = basename($file);
    try {
        $value = include $file;
        $type = gettype($value);
        echo "$name: $type\n";
        if (is_array($value)) {
            echo "  keys: " . implode(', ', array_keys($value)) . "\n";
        } else {
            echo "  value: ";
            var_export($value);
            echo "\n";
        }
    } catch (Throwable $e) {
        echo "$name: EXCEPTION - " . $e->getMessage() . "\n";
    }
}
