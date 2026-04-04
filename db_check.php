<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=agrimans;charset=utf8', 'root', '');
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $t) {
        if (stripos($t, 'equip') !== false || stripos($t, 'review') !== false) {
            echo "TABLE: $t\n";
            $desc = $pdo->query("DESCRIBE $t")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($desc as $row) { echo " - {$row['Field']} ({$row['Type']})\n"; }
        }
    }
} catch (Exception $e) { echo "ERROR: " . $e->getMessage(); }
