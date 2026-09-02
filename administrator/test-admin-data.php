<?php
define('ADMIN_APP', true);
require 'E:\mydata\website\sohni\administrator\config.php';
require 'E:\mydata\website\sohni\administrator\app\Database.php';

echo "=== Admin Users ===\n";
$users = Database::all('SELECT id, name, email, role, status FROM admin_users ORDER BY created_at DESC');
if ($users) {
    foreach ($users as $u) {
        echo "- {$u['name']} ({$u['email']}) - Role: {$u['role']}, Status: {$u['status']}\n";
    }
} else {
    echo "No admin users found\n";
}

echo "\n=== Admin Sessions ===\n";
$sessions = Database::all('SELECT id, admin_user_id, revoked, last_activity FROM admin_sessions LIMIT 10');
if ($sessions) {
    foreach ($sessions as $s) {
        echo "- Session {$s['id']}: admin_user_id={$s['admin_user_id']}, revoked={$s['revoked']}\n";
    }
} else {
    echo "No admin sessions found\n";
}
