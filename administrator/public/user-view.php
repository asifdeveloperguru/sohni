<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$admin = Auth::require('moderator');

$userId = (int) query('id', 0);
$user = Database::one('SELECT * FROM users WHERE id = ?', [$userId]);
if (! $user) {
    http_response_code(404);
    exit('User not found.');
}

if (is_post()) {
    Security::verifyCsrf();
    $action = (string) post('action', '');

    if ($action === 'toggle_verify' && Auth::hasRole('moderator')) {
        $verified = $user['email_verified_at'] ? null : date('Y-m-d H:i:s');
        Database::run('UPDATE users SET email_verified_at = ? WHERE id = ?', [$verified, $userId]);
        Audit::log($admin['id'], $verified ? 'verify_user' : 'unverify_user', 'user', $userId);
        flash('success', $verified ? 'User marked as verified.' : 'Verification removed.');
    }

    if ($action === 'suspend' && Auth::hasRole('moderator')) {
        $days = max(1, (int) post('days', 7));
        $reason = trim((string) post('reason', ''));
        $until = date('Y-m-d H:i:s', time() + $days * 86400);
        Database::run('UPDATE users SET is_suspended = 1, suspended_until = ? WHERE id = ?', [$until, $userId]);
        Audit::log($admin['id'], 'suspend_user', 'user', $userId, ['days' => $days, 'reason' => $reason]);
        flash('success', "User suspended for {$days} day(s).");
    }

    if ($action === 'unsuspend' && Auth::hasRole('moderator')) {
        Database::run('UPDATE users SET is_suspended = 0, suspended_until = NULL WHERE id = ?', [$userId]);
        Audit::log($admin['id'], 'unsuspend_user', 'user', $userId);
        flash('success', 'Suspension lifted.');
    }

    if ($action === 'ban' && Auth::hasRole('admin')) {
        $reason = trim((string) post('reason', ''));
        Database::run('UPDATE users SET is_banned = 1, ban_reason = ?, banned_at = datetime("now"), banned_by = ? WHERE id = ?', [$reason ?: null, $admin['id'], $userId]);
        Database::run('DELETE FROM sessions WHERE user_id = ?', [$userId]);
        Audit::log($admin['id'], 'ban_user', 'user', $userId, ['reason' => $reason]);
        flash('success', 'User banned and signed out everywhere.');
    }

    if ($action === 'unban' && Auth::hasRole('admin')) {
        Database::run('UPDATE users SET is_banned = 0, ban_reason = NULL, banned_at = NULL, banned_by = NULL WHERE id = ?', [$userId]);
        Audit::log($admin['id'], 'unban_user', 'user', $userId);
        flash('success', 'User unbanned.');
    }

    if ($action === 'force_logout' && Auth::hasRole('admin')) {
        $count = Database::run('DELETE FROM sessions WHERE user_id = ?', [$userId]);
        Audit::log($admin['id'], 'force_logout_user', 'user', $userId, ['sessions_ended' => $count]);
        flash('success', "Signed out of {$count} active session(s).");
    }

    if ($action === 'soft_delete' && Auth::hasRole('super_admin')) {
        Database::run('UPDATE users SET deleted_at = datetime("now") WHERE id = ?', [$userId]);
        Audit::log($admin['id'], 'soft_delete_user', 'user', $userId);
        flash('success', 'Account deleted (soft — can be restored).');
    }

    if ($action === 'restore' && Auth::hasRole('super_admin')) {
        Database::run('UPDATE users SET deleted_at = NULL WHERE id = ?', [$userId]);
        Audit::log($admin['id'], 'restore_user', 'user', $userId);
        flash('success', 'Account restored.');
    }

    redirect('/user-view.php?id=' . $userId);
}

$blockedIds = json_decode($user['blocked_users'] ?? '[]', true) ?: [];
$blockedUsers = $blockedIds ? Database::all('SELECT id, name, email FROM users WHERE id IN (' . implode(',', array_fill(0, count($blockedIds), '?')) . ')', $blockedIds) : [];

$sessions = Database::all('SELECT id, ip_address, user_agent, last_activity FROM sessions WHERE user_id = ? ORDER BY last_activity DESC LIMIT 10', [$userId]);
$conversationCount = Database::scalar('SELECT COUNT(*) FROM conversation_user WHERE user_id = ?', [$userId]);
$messageCount = Database::scalar('SELECT COUNT(*) FROM messages WHERE user_id = ?', [$userId]);
$reportsAgainst = Database::scalar('SELECT COUNT(*) FROM reports WHERE reported_user_id = ?', [$userId]);
$reportsFiled = Database::scalar('SELECT COUNT(*) FROM reports WHERE reporter_id = ?', [$userId]);

$displayName = LaravelCrypt::displayOrFallback($user['name'], 'Unnamed');
$phone = LaravelCrypt::displayOrFallback($user['phone'] ?? null, '—');
$address = LaravelCrypt::displayOrFallback($user['address'] ?? null, '—');
$about = LaravelCrypt::displayOrFallback($user['about_me'] ?? null, '—');

$pageTitle = $displayName;
$activeNav = 'users';
require __DIR__ . '/../app/partials/header.php';
?>

<div class="panel">
    <div class="panel-head">
        <h2>Account overview</h2>
        <div class="action-row">
            <?php if ($user['is_banned'] ?? false): ?><span class="pill red">Banned</span>
            <?php elseif ($user['is_suspended'] ?? false): ?><span class="pill amber">Suspended until <?= h($user['suspended_until'] ?? '') ?></span>
            <?php elseif ($user['email_verified_at'] ?? false): ?><span class="pill green">Verified</span>
            <?php else: ?><span class="pill gray">Unverified</span><?php endif; ?>
            <?php if ($user['deleted_at'] ?? false): ?><span class="pill red">Deleted</span><?php endif; ?>
        </div>
    </div>
    <div class="panel-body">
        <div class="detail-grid">
            <div class="detail-item"><label>Name</label><div><?= h($displayName) ?></div></div>
            <div class="detail-item"><label>Email</label><div><?= h($user['email']) ?></div></div>
            <div class="detail-item"><label>Phone</label><div><?= h($phone) ?></div></div>
            <div class="detail-item"><label>Sohni ID</label><div><code class="mono"><?= h($user['sohni_id'] ?? '—') ?></code></div>
            </div>
            <div class="detail-item"><label>Address</label><div><?= h($address) ?></div></div>
            <div class="detail-item"><label>Joined</label><div><?= h($user['created_at']) ?></div></div>
            <div class="detail-item"><label>Last seen</label><div><?= h(time_ago($user['last_seen_at'] ?? null)) ?></div></div>
            <div class="detail-item"><label>Friends / Followers / Groups</label><div><?= (int) ($user['friends_count'] ?? 0) ?> / <?= (int) ($user['followers_count'] ?? 0) ?> / <?= (int) ($user['groups_count'] ?? 0) ?></div></div>
            <div class="detail-item"><label>Conversations</label><div><?= (int) $conversationCount ?></div></div>
            <div class="detail-item"><label>Messages sent</label><div><?= (int) $messageCount ?></div></div>
            <div class="detail-item"><label>Reports against this user</label><div><?= (int) $reportsAgainst ?></div></div>
            <div class="detail-item"><label>Reports filed by this user</label><div><?= (int) $reportsFiled ?></div></div>
        </div>
        <?php if ($about !== '—'): ?><p style="margin-top:16px;color:var(--muted);font-size:13.5px;"><?= h($about) ?></p><?php endif; ?>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h2>Moderation actions</h2></div>
    <div class="panel-body action-row">
        <form method="post" onsubmit="return confirm('Toggle verification status?')">
            <?= Security::csrfField() ?><input type="hidden" name="action" value="toggle_verify">
            <button class="btn" type="submit"><i class="fas fa-circle-check"></i> <?= ($user['email_verified_at'] ?? false) ? 'Remove verification' : 'Mark verified' ?></button>
        </form>

        <?php if ($user['is_suspended'] ?? false): ?>
            <form method="post">
                <?= Security::csrfField() ?><input type="hidden" name="action" value="unsuspend">
                <button class="btn" type="submit"><i class="fas fa-play"></i> Lift suspension</button>
            </form>
        <?php else: ?>
            <form method="post" style="display:flex;gap:6px;align-items:center" onsubmit="return confirm('Suspend this user?')">
                <?= Security::csrfField() ?><input type="hidden" name="action" value="suspend">
                <input type="number" name="days" value="7" min="1" max="365" style="width:64px;padding:9px;border:1px solid var(--line);border-radius:10px" title="Days">
                <input type="text" name="reason" placeholder="Reason" style="padding:9px;border:1px solid var(--line);border-radius:10px">
                <button class="btn" type="submit"><i class="fas fa-pause"></i> Suspend</button>
            </form>
        <?php endif; ?>

        <?php if (Auth::hasRole('admin')): ?>
            <?php if ($user['is_banned'] ?? false): ?>
                <form method="post" onsubmit="return confirm('Unban this user?')">
                    <?= Security::csrfField() ?><input type="hidden" name="action" value="unban">
                    <button class="btn" type="submit"><i class="fas fa-unlock"></i> Unban</button>
                </form>
            <?php else: ?>
                <form method="post" style="display:flex;gap:6px;align-items:center" onsubmit="return confirm('Ban this user permanently? This also signs them out everywhere.')">
                    <?= Security::csrfField() ?><input type="hidden" name="action" value="ban">
                    <input type="text" name="reason" placeholder="Reason" style="padding:9px;border:1px solid var(--line);border-radius:10px">
                    <button class="btn danger" type="submit"><i class="fas fa-ban"></i> Ban</button>
                </form>
            <?php endif; ?>
            <form method="post" onsubmit="return confirm('End every active session for this user?')">
                <?= Security::csrfField() ?><input type="hidden" name="action" value="force_logout">
                <button class="btn" type="submit"><i class="fas fa-right-from-bracket"></i> Force logout everywhere</button>
            </form>
        <?php endif; ?>

        <?php if (Auth::hasRole('super_admin')): ?>
            <?php if ($user['deleted_at'] ?? false): ?>
                <form method="post"><?= Security::csrfField() ?><input type="hidden" name="action" value="restore">
                    <button class="btn" type="submit"><i class="fas fa-trash-arrow-up"></i> Restore account</button>
                </form>
            <?php else: ?>
                <form method="post" onsubmit="return confirm('Soft-delete this account? It can be restored later.')">
                    <?= Security::csrfField() ?><input type="hidden" name="action" value="soft_delete">
                    <button class="btn danger" type="submit"><i class="fas fa-trash"></i> Delete account</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h2>Blocked users (by this account)</h2></div>
    <table class="grid">
        <thead><tr><th>Name</th><th>Email</th></tr></thead>
        <tbody>
        <?php foreach ($blockedUsers as $b): ?>
            <tr><td><a href="/user-view.php?id=<?= (int) $b['id'] ?>"><?= h(LaravelCrypt::displayOrFallback($b['name'], 'Unnamed')) ?></a></td><td><?= h($b['email']) ?></td></tr>
        <?php endforeach; if (! $blockedUsers): ?>
            <tr><td colspan="2" class="empty-row">No blocked users.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="panel">
    <div class="panel-head"><h2>Active sessions</h2></div>
    <table class="grid">
        <thead><tr><th>IP address</th><th>Device / browser</th><th>Last activity</th></tr></thead>
        <tbody>
        <?php foreach ($sessions as $s): ?>
            <tr><td><?= h($s['ip_address']) ?></td><td><?= h(mb_substr($s['user_agent'] ?? '', 0, 60)) ?></td><td><?= h(time_ago(date('Y-m-d H:i:s', (int) $s['last_activity']))) ?></td></tr>
        <?php endforeach; if (! $sessions): ?>
            <tr><td colspan="3" class="empty-row">No active sessions.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../app/partials/footer.php'; ?>
