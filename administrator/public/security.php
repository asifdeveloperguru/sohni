<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$admin = Auth::require('moderator');
$config = require __DIR__ . '/../config.php';

$recoveryCodesToShow = null;

if (is_post()) {
    Security::verifyCsrf();
    $action = (string) post('action', '');

    if ($action === 'change_password') {
        $current = (string) post('current_password', '');
        $new = (string) post('new_password', '');
        $confirm = (string) post('confirm_password', '');

        if (! password_verify($current, $admin['password'])) {
            flash('error', 'Current password is incorrect.');
        } elseif (strlen($new) < 10) {
            flash('error', 'New password must be at least 10 characters.');
        } elseif ($new !== $confirm) {
            flash('error', 'New password confirmation does not match.');
        } else {
            Database::run('UPDATE admin_users SET password = ? WHERE id = ?', [password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]), $admin['id']]);
            Audit::log($admin['id'], 'change_own_password');
            flash('success', 'Password updated.');
        }
    }

    if ($action === 'start_2fa') {
        $secret = Totp::generateSecret();
        $_SESSION['_pending_totp_secret'] = $secret;
    }

    if ($action === 'confirm_2fa') {
        $secret = $_SESSION['_pending_totp_secret'] ?? null;
        if ($secret && Totp::verify($secret, (string) post('code', ''), $config['totp_window'])) {
            $codes = Totp::generateRecoveryCodes();
            Database::run('UPDATE admin_users SET totp_secret = ?, totp_enabled = 1, recovery_codes = ? WHERE id = ?', [$secret, json_encode($codes), $admin['id']]);
            unset($_SESSION['_pending_totp_secret']);
            Audit::log($admin['id'], 'enable_2fa');
            $_SESSION['_show_recovery_codes'] = $codes;
            flash('success', 'Two-factor authentication is now enabled.');
        } else {
            flash('error', 'That code did not match. Try again.');
        }
    }

    if ($action === 'disable_2fa') {
        if (password_verify((string) post('confirm_password', ''), $admin['password'])) {
            Database::run('UPDATE admin_users SET totp_secret = NULL, totp_enabled = 0, recovery_codes = NULL WHERE id = ?', [$admin['id']]);
            Audit::log($admin['id'], 'disable_2fa');
            flash('success', 'Two-factor authentication disabled.');
        } else {
            flash('error', 'Incorrect password — 2FA was not disabled.');
        }
    }

    if ($action === 'revoke_session') {
        $sid = (string) post('session_id', '');
        if ($sid !== session_id()) {
            Database::run('DELETE FROM admin_sessions WHERE id = ? AND admin_user_id = ?', [$sid, $admin['id']]);
            Audit::log($admin['id'], 'revoke_own_session');
            flash('success', 'Session revoked.');
        }
    }

    redirect('/security.php');
}

$recoveryCodesToShow = $_SESSION['_show_recovery_codes'] ?? null;
unset($_SESSION['_show_recovery_codes']);

$pendingSecret = $_SESSION['_pending_totp_secret'] ?? null;
$mySessions = Database::all('SELECT * FROM admin_sessions WHERE admin_user_id = ? ORDER BY last_activity DESC', [$admin['id']]);

$pageTitle = 'My security';
$activeNav = 'security';
require __DIR__ . '/../app/partials/header.php';
?>

<?php if ($recoveryCodesToShow): ?>
<div class="panel">
    <div class="panel-head"><h2>Save your recovery codes</h2></div>
    <div class="panel-body">
        <p style="font-size:13.5px;color:var(--muted);">Each code can be used once if you lose access to your authenticator app. They will not be shown again.</p>
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;font-family:ui-monospace,monospace;font-size:13px;background:var(--canvas);padding:14px;border-radius:10px;">
            <?php foreach ($recoveryCodesToShow as $code): ?><div><?= h($code) ?></div><?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="panel">
    <div class="panel-head"><h2>Change password</h2></div>
    <div class="panel-body">
        <form method="post" style="max-width:360px;">
            <?= Security::csrfField() ?><input type="hidden" name="action" value="change_password">
            <div class="field"><label>Current password</label><input type="password" name="current_password" autocomplete="current-password" required></div>
            <div class="field"><label>New password</label><input type="password" name="new_password" autocomplete="new-password" minlength="10" required></div>
            <div class="field"><label>Confirm new password</label><input type="password" name="confirm_password" autocomplete="new-password" minlength="10" required></div>
            <button class="btn primary" type="submit">Update password</button>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h2>Two-factor authentication</h2></div>
    <div class="panel-body">
        <?php if ($admin['totp_enabled']): ?>
            <p class="pill green" style="margin-bottom:14px;">Enabled</p>
            <form method="post" style="max-width:320px;" onsubmit="return confirm('Disable two-factor authentication?')">
                <?= Security::csrfField() ?><input type="hidden" name="action" value="disable_2fa">
                <div class="field"><label>Confirm your password</label><input type="password" name="confirm_password" required></div>
                <button class="btn danger" type="submit">Disable 2FA</button>
            </form>
        <?php elseif ($pendingSecret): ?>
            <p style="font-size:13.5px;color:var(--muted);margin-bottom:10px;">
                Add this key manually in your authenticator app (Google Authenticator, Authy, 1Password…).
                We don't render a QR code so the secret never leaves this server.
            </p>
            <div class="detail-grid" style="margin-bottom:14px;">
                <div class="detail-item"><label>Account</label><div><?= h($admin['email']) ?></div></div>
                <div class="detail-item"><label>Secret key</label><div><code class="mono"><?= h($pendingSecret) ?></code></div></div>
                <div class="detail-item"><label>Type</label><div>Time-based, 6 digits, 30s</div></div>
            </div>
            <form method="post" style="max-width:300px;display:flex;gap:8px;align-items:end;">
                <?= Security::csrfField() ?><input type="hidden" name="action" value="confirm_2fa">
                <div class="field" style="margin:0;flex:1;"><label>Enter the 6-digit code to confirm</label><input type="text" name="code" inputmode="numeric" maxlength="6" required></div>
                <button class="btn primary" type="submit">Confirm</button>
            </form>
        <?php else: ?>
            <p class="pill gray" style="margin-bottom:14px;">Disabled</p>
            <form method="post"><?= Security::csrfField() ?><input type="hidden" name="action" value="start_2fa">
                <button class="btn primary" type="submit"><i class="fas fa-shield-halved"></i> Enable 2FA</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h2>My active sessions</h2></div>
    <table class="grid">
        <thead><tr><th>IP</th><th>Device</th><th>Last activity</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($mySessions as $s): $isCurrent = $s['id'] === session_id(); ?>
            <tr>
                <td><?= h($s['ip_address']) ?></td>
                <td><?= h(mb_substr($s['user_agent'] ?? '', 0, 60)) ?></td>
                <td><?= h(time_ago($s['last_activity'])) ?></td>
                <td>
                    <?php if ($isCurrent): ?><span class="pill blue">This device</span>
                    <?php else: ?>
                        <form method="post" onsubmit="return confirm('Sign out this session?')">
                            <?= Security::csrfField() ?><input type="hidden" name="action" value="revoke_session">
                            <input type="hidden" name="session_id" value="<?= h($s['id']) ?>">
                            <button class="btn sm danger" type="submit">Sign out</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../app/partials/footer.php'; ?>
