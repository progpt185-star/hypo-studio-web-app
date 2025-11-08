<?php
$db = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
$tables = ['users','customers','orders'];
foreach ($tables as $t) {
    try {
        $stmt = $db->query("SELECT COUNT(*) as c FROM $t");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "$t:" . ($row['c'] ?? '0') . "\n";
    } catch (Exception $e) {
        echo "$t: ERROR (" . $e->getMessage() . ")\n";
    }
}
