<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$admin = Auth::require('super_admin');

$id = (int) query('id', 0);
$target = Database::one('SELECT * FROM admin_users WHERE id = ?', [$id]);
if (! $target) {
    http_response_code(404);
    exit('Admin not found.');
}

if (is_post()) {
    Security::verifyCsrf();
    $action = (string) post('action', '');
    $isSelf = $id === (int) $admin['id'];

    if ($action === 'change_role' && ! $isSelf && in_array(post('role'), ['moderator', 'admin', 'super_admin'], true)) {
        Database::run('UPDATE admin_users SET role = ? WHERE id = ?', [post('role'), $id]);
        Audit::log($admin['id'], 'change_admin_role', 'admin_user', $id, ['role' => post('role')]);
        flash('success', 'Role updated.');
    }

    if ($action === 'toggle_status' && ! $isSelf) {
        $newStatus = $target['status'] === 'active' ? 'suspended' : 'active';
        Database::run('UPDATE admin_users SET status = ? WHERE id = ?', [$newStatus, $id]);
        if ($newStatus === 'suspended') {
            Database::run('DELETE FROM admin_sessions WHERE admin_user_id = ?', [$id]);
        }
        Audit::log($admin['id'], 'toggle_admin_status', 'admin_user', $id, ['status' => $newStatus]);
        flash('success', "Admin {$newStatus}.");
    }

    if ($action === 'reset_2fa' && ! $isSelf) {
        Database::run('UPDATE admin_users SET totp_enabled = 0, totp_secret = NULL, recovery_codes = NULL WHERE id = ?', [$id]);
        Audit::log($admin['id'], 'reset_admin_2fa', 'admin_user', $id);
        flash('success', "Two-factor authentication reset for this admin. They'll be prompted to set it up again if you re-enable it.");
    }

    if ($action === 'revoke_sessions') {
        Database::run('DELETE FROM admin_sessions WHERE admin_user_id = ?', [$id]);
        Audit::log($admin['id'], 'revoke_admin_sessions', 'admin_user', $id);
        flash('success', 'All sessions for this admin were revoked.');
    }

    redirect('/admin-view.php?id=' . $id);
}

$sessions = Database::all('SELECT * FROM admin_sessions WHERE admin_user_id = ? ORDER BY last_activity DESC', [$id]);
$recentActions = Database::all('SELECT * FROM admin_audit_logs WHERE admin_user_id = ? ORDER BY created_at DESC LIMIT 20', [$id]);

$pageTitle = $target['name'];
$activeNav = 'admins';
require __DIR__ . '/../app/partials/header.php';
?>

<div class="panel">
    <div class="panel-head">
        <h2>Account</h2>
        <span class="pill <?= $target['status'] === 'active' ? 'green' : 'red' ?>"><?= h(ucfirst($target['status'])) ?></span>
    </div>
    <div class="panel-body">
        <div class="detail-grid">
            <div class="detail-item"><label>Email</label><div><?= h($target['email']) ?></div></div>
            <div class="detail-item"><label>Role</label><div><?= h(str_replace('_', ' ', ucfirst($target['role']))) ?></div></div>
            <div class="detail-item"><label>2FA</label><div><?= $target['totp_enabled'] ? 'Enabled' : 'Disabled' ?></div></div>
            <div class="detail-item"><label>Last login</label><div><?= h(time_ago($target['last_login_at'])) ?> from <?= h($target['last_login_ip'] ?? '—') ?></div></div>
            <div class="detail-item"><label>Created</label><div><?= h($target['created_at']) ?></div></div>
        </div>
    </div>
</div>

<?php if ($id !== (int) $admin['id']): ?>
<div class="panel">
    <div class="panel-head"><h2>Manage</h2></div>
    <div class="panel-body action-row">
        <form method="post" style="display:flex;gap:6px;">
            <?= Security::csrfField() ?><input type="hidden" name="action" value="change_role">
            <select name="role">
                <option value="moderator" <?= $target['role'] === 'moderator' ? 'selected' : '' ?>>Moderator</option>
                <option value="admin" <?= $target['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="super_admin" <?= $target['role'] === 'super_admin' ? 'selected' : '' ?>>Super admin</option>
            </select>
            <button class="btn" type="submit">Change role</button>
        </form>
        <form method="post" onsubmit="return confirm('<?= $target['status'] === 'active' ? 'Suspend' : 'Reactivate' ?> this admin?')">
            <?= Security::csrfField() ?><input type="hidden" name="action" value="toggle_status">
            <button class="btn <?= $target['status'] === 'active' ? 'danger' : '' ?>" type="submit">
                <?= $target['status'] === 'active' ? 'Suspend admin' : 'Reactivate admin' ?>
            </button>
        </form>
        <form method="post" onsubmit="return confirm('Reset this admin\'s 2FA? They will need to set it up again.')">
            <?= Security::csrfField() ?><input type="hidden" name="action" value="reset_2fa">
            <button class="btn" type="submit">Reset 2FA</button>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="panel">
    <div class="panel-head">
        <h2>Sessions</h2>
        <?php if ($sessions): ?>
        <form method="post" onsubmit="return confirm('Revoke all sessions for this admin?')">
            <?= Security::csrfField() ?><input type="hidden" name="action" value="revoke_sessions">
            <button class="btn sm danger" type="submit">Revoke all</button>
        </form>
        <?php endif; ?>
    </div>
    <table class="grid">
        <thead><tr><th>IP</th><th>Device</th><th>Last activity</th></tr></thead>
        <tbody>
        <?php foreach ($sessions as $s): ?>
            <tr><td><?= h($s['ip_address']) ?></td><td><?= h(mb_substr($s['user_agent'] ?? '', 0, 60)) ?></td><td><?= h(time_ago($s['last_activity'])) ?></td></tr>
        <?php endforeach; if (! $sessions): ?>
            <tr><td colspan="3" class="empty-row">No active sessions.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="panel">
    <div class="panel-head"><h2>Recent activity by this admin</h2></div>
    <table class="grid">
        <thead><tr><th>Action</th><th>Target</th><th>When</th></tr></thead>
        <tbody>
        <?php foreach ($recentActions as $a): ?>
            <tr><td><code class="mono"><?= h($a['action']) ?></code></td><td><?= $a['target_type'] ? h($a['target_type'] . ' #' . $a['target_id']) : '—' ?></td><td><?= h(time_ago($a['created_at'])) ?></td></tr>
        <?php endforeach; if (! $recentActions): ?>
            <tr><td colspan="3" class="empty-row">No activity yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../app/partials/footer.php'; ?>
