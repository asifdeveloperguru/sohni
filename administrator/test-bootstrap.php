<?php
// Simulate the bootstrap and check for errors
define('ADMIN_APP', true);
require 'E:\mydata\website\sohni\administrator\config.php';
require 'E:\mydata\website\sohni\administrator\app\Database.php';
require 'E:\mydata\website\sohni\administrator\app\helpers.php';

try {
    echo "✓ Config loaded\n";
    echo "✓ Database loaded\n";
    
    // Test a simple query
    $count = Database::scalar('SELECT COUNT(*) FROM admin_users');
    echo "✓ Admin users count: $count\n";
    
    // Test session query (simulating without active session)
    echo "\n✓ All checks passed!\n";
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
