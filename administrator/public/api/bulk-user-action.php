<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$admin = Auth::require('moderator');

// Parse request
$action = $_POST['action'] ?? '';
$ids = array_filter(array_map('intval', explode(',', $_POST['ids'] ?? '')));

if (!$action || !$ids) {
    http_response_code(400);
    die('Invalid request.');
}

$count = count($ids);
$affected = 0;

match ($action) {
    'verify' => function() use (&$affected, $ids, $admin) {
        foreach ($ids as $uid) {
            if (Database::exec(
                'UPDATE users SET email_verified_at = CURRENT_TIMESTAMP WHERE id = ? AND email_verified_at IS NULL',
                [$uid]
            )) {
                $affected++;
                Audit::log('verify_user', 'user', $uid, null, ['admin_id' => $admin['id']]);
            }
        }
    }(),
    'suspend' => function() use (&$affected, $ids, $admin) {
        $until = date('Y-m-d H:i:s', time() + 7 * 86400);
        foreach ($ids as $uid) {
            if (Database::exec(
                'UPDATE users SET is_suspended = 1, suspended_until = ?, is_suspended_reason = ? WHERE id = ? AND is_suspended = 0',
                [$until, 'Admin bulk suspend', $uid]
            )) {
                $affected++;
                Audit::log('suspend_user', 'user', $uid, null, ['admin_id' => $admin['id'], 'days' => 7]);
            }
        }
    }(),
    'ban' => function() use (&$affected, $ids, $admin) {
        foreach ($ids as $uid) {
            if (Database::exec(
                'UPDATE users SET is_banned = 1, ban_reason = ? WHERE id = ? AND is_banned = 0',
                ['Admin bulk ban', $uid]
            )) {
                $affected++;
                Audit::log('ban_user', 'user', $uid, null, ['admin_id' => $admin['id']]);
            }
        }
    }(),
    default => null,
};

flash('success', "$affected of $count user(s) updated.");
redirect('/users-v2.php');
