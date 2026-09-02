<?php
$realpath = realpath(__DIR__ . '/../frontend/database/database.sqlite');
echo "Realpath result: ";
var_dump($realpath);

$fallback = __DIR__ . '/../frontend/database/database.sqlite';
echo "\nFallback path: $fallback\n";

$absoluteFallback = __DIR__ . '/../frontend/database/database.sqlite';
echo "Absolute path from admin: " . realpath($absoluteFallback) . "\n";

// Test PDO connection
try {
    $dbPath = $realpath ?: $fallback;
    echo "\nUsing: $dbPath\n";
    
    $pdo = new PDO("sqlite:$dbPath", null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' LIMIT 5")->fetchAll(PDO::FETCH_COLUMN);
    echo "Successfully connected. Found tables: " . implode(', ', $tables) . "\n";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
