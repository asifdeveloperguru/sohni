<?php
$dbPath = 'E:\mydata\website\sohni\frontend\database\database.sqlite';
$pdo = new PDO("sqlite:$dbPath", null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

echo "=== ALL TABLES ===\n";
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $t) {
    echo "- $t\n";
}

echo "\n=== ADMIN_SESSIONS CHECK ===\n";
try {
    $result = $pdo->query("PRAGMA table_info(admin_sessions)")->fetchAll(PDO::FETCH_ASSOC);
    if ($result) {
        echo "✓ admin_sessions exists with " . count($result) . " columns\n";
        foreach ($result as $col) {
            echo "  - {$col['name']}\n";
        }
    } else {
        echo "✗ admin_sessions table not found\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
