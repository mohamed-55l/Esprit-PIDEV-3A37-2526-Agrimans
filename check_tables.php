<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=agrimans', 'root', '');
$result = $pdo->query('SHOW TABLES');
echo "Tables in agrimans database:\n";
while ($row = $result->fetch(PDO::FETCH_NUM)) {
    echo "  - " . $row[0] . "\n";
}
?>
