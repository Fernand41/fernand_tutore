<?php
require 'config/database.php';
try {
    $pdo = Database::getInstance();
    $stmt = $pdo->query('DESCRIBE recettes');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . '|' . $row['Type'] . '|' . $row['Null'] . '|' . ($row['Key'] ?: '') . '|' . ($row['Default'] ?? 'NULL') . "\n";
    }
} catch (Exception $e) {
    echo 'ERR: ' . $e->getMessage();
}
