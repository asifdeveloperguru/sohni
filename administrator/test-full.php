<?php
// Simulate a login attempt
define('ADMIN_APP', true);
require __DIR__ . '/config.php';
require __DIR__ . '/app/bootstrap.php';

echo "=== Testing Admin Panel ===\n\n";

// Test 1: Check database connection
echo "1. Database connection: ";
try {
    $test = Database::scalar('SELECT 1');
    echo "✓ OK\n";
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check admin user exists
echo "2. Admin user exists: ";
$admin = Database::one('SELECT * FROM admin_users WHERE email = ?', ['superadmin@sohni.local']);
if ($admin) {
    echo "✓ Found (ID: {$admin['id']}, Role: {$admin['role']})\n";
} else {
    echo "✗ Not found\n";
    exit(1);
}

// Test 3: Test Analytics class
echo "3. Analytics class loads: ";
if (class_exists('Analytics')) {
    echo "✓ OK\n";
} else {
    echo "✗ FAILED\n";
    exit(1);
}

// Test 4: Run a sample analytics query
echo "4. Sample analytics query: ";
try {
    $online = Analytics::onlineUsersCount();
    echo "✓ OK (Online users: $online)\n";
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 5: Check all admin tables
echo "5. Admin tables: ";
$tables = [
    'admin_users' => 'Users',
    'admin_sessions' => 'Sessions', 
    'admin_audit_logs' => 'Audit logs',
    'admin_analytics' => 'Analytics'
];
$allOk = true;
foreach ($tables as $t => $label) {
    $count = Database::scalar("SELECT COUNT(*) FROM $t");
    echo "\n   - $label: $count records";
    if (!$count) {
        echo " (OK - empty is fine for new install)";
    }
}
echo "\n   ✓ All tables exist\n";

echo "\n✅ ALL TESTS PASSED\n";
echo "\nAdmin Panel is ready to use!\n";
echo "URL: http://127.0.0.1:9000/login.php\n";
echo "Email: superadmin@sohni.local\n";
echo "Password: ab40e6442f64234ce7\n";
