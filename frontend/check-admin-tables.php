<?php
$db = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== Admin Tables ===\n";
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name LIKE 'admin_%' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
if ($tables) {
    foreach ($tables as $t) {
        echo "✓ $t\n";
    }
} else {
    echo "❌ NO ADMIN TABLES FOUND\n";
}

echo "\n=== Admin Sessions Schema ===\n";
try {
    $cols = $db->query("PRAGMA table_info(admin_sessions)")->fetchAll(PDO::FETCH_ASSOC);
    if ($cols) {
        foreach ($cols as $c) {
            echo "- {$c['name']} ({$c['type']})\n";
        }
    } else {
        echo "❌ Table exists but no columns?\n";
    }
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Admin Users Schema ===\n";
try {
    $cols = $db->query("PRAGMA table_info(admin_users)")->fetchAll(PDO::FETCH_ASSOC);
    if ($cols) {
        foreach ($cols as $c) {
            echo "- {$c['name']} ({$c['type']})\n";
        }
    } else {
        echo "❌ Table exists but no columns?\n";
    }
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
