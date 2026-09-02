<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$admin = Auth::require('moderator');

$ids = array_filter(array_map('intval', explode(',', $_GET['ids'] ?? '')));

if (!$ids) {
    http_response_code(400);
    die('No users selected.');
}

$users = Database::all(
    'SELECT id, name, email, sohni_id, login_count, data_usage_bytes, email_verified_at, 
            is_banned, is_suspended, created_at, last_seen_at 
     FROM users WHERE id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')',
    $ids
);

// CSV output
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="users-export-' . date('Y-m-d-His') . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['ID', 'Name', 'Email', 'Sohni ID', 'Logins', 'Data Usage (bytes)', 'Email Verified', 'Banned', 'Suspended', 'Created', 'Last Seen']);

foreach ($users as $u) {
    fputcsv($out, [
        $u['id'],
        LaravelCrypt::displayOrFallback($u['name'], 'Unnamed'),
        $u['email'],
        $u['sohni_id'] ?? '',
        $u['login_count'],
        $u['data_usage_bytes'],
        $u['email_verified_at'] ? 'Yes' : 'No',
        $u['is_banned'] ? 'Yes' : 'No',
        $u['is_suspended'] ? 'Yes' : 'No',
        $u['created_at'],
        $u['last_seen_at'] ?? '',
    ]);
}

fclose($out);
