<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$admin = Auth::require('moderator');

$adminId = Auth::id();

if (is_post()) {
    Security::verifyCsrf();
    $action = (string) post('action', '');

    if ($action === 'update_profile' && Auth::hasRole('moderator')) {
        $name = trim((string) post('name', ''));
        $email = trim((string) post('email', ''));

        if (!$name) {
            flash('error', 'Name is required.');
            redirect('/admin-settings.php');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Invalid email address.');
            redirect('/admin-settings.php');
        }

        // Check if email is already taken by another admin
        $existing = Database::one('SELECT id FROM admin_users WHERE email = ? AND id != ?', [$email, $adminId]);
        if ($existing) {
            flash('error', 'Email already in use by another admin.');
            redirect('/admin-settings.php');
        }

        Database::run('UPDATE admin_users SET name = ?, email = ? WHERE id = ?', [$name, $email, $adminId]);
        Audit::log($adminId, 'update_profile', 'admin', $adminId, ['field' => 'name, email']);
        flash('success', 'Profile updated successfully.');
        redirect('/admin-settings.php');
    }

    if ($action === 'change_password' && Auth::hasRole('moderator')) {
        $currentPassword = (string) post('current_password', '');
        $newPassword = (string) post('new_password', '');
        $confirmPassword = (string) post('confirm_password', '');

        // Verify current password
        $adminRecord = Database::one('SELECT password FROM admin_users WHERE id = ?', [$adminId]);
        if (!$adminRecord || !password_verify($currentPassword, $adminRecord['password'])) {
            flash('error', 'Current password is incorrect.');
            redirect('/admin-settings.php');
        }

        if (strlen($newPassword) < 12) {
            flash('error', 'New password must be at least 12 characters.');
            redirect('/admin-settings.php');
        }

        if ($newPassword !== $confirmPassword) {
            flash('error', 'Passwords do not match.');
            redirect('/admin-settings.php');
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        Database::run('UPDATE admin_users SET password = ? WHERE id = ?', [$hashedPassword, $adminId]);
        Audit::log($adminId, 'change_password', 'admin', $adminId);
        flash('success', 'Password changed successfully.');
        redirect('/admin-settings.php');
    }

    if ($action === 'setup_totp' && Auth::hasRole('moderator')) {
        // Generate new TOTP secret
        $secret = Totp::generateSecret();
        $_SESSION['pending_totp_secret'] = $secret;
        $_SESSION['pending_totp_time'] = time();
        flash('success', 'TOTP secret generated. Scan the QR code or enter the secret manually, then verify.');
        redirect('/admin-settings.php');
    }

    if ($action === 'verify_totp' && Auth::hasRole('moderator')) {
        $code = trim((string) post('totp_code', ''));
        $secret = $_SESSION['pending_totp_secret'] ?? null;

        if (!$secret) {
            flash('error', 'No pending TOTP setup. Start setup again.');
            redirect('/admin-settings.php');
        }

        if (!Totp::verify($code, $secret)) {
            flash('error', 'Invalid code. Please try again.');
            redirect('/admin-settings.php');
        }

        // Generate recovery codes
        $recoveryCodes = Totp::generateRecoveryCodes();
        Database::run(
            'UPDATE admin_users SET totp_secret = ?, totp_enabled = 1, recovery_codes = ? WHERE id = ?',
            [$secret, json_encode($recoveryCodes), $adminId]
        );
        Audit::log($adminId, 'enable_2fa', 'admin', $adminId);

        // Clear pending setup
        unset($_SESSION['pending_totp_secret'], $_SESSION['pending_totp_time']);
        
        flash('success', 'Two-factor authentication enabled!');
        $_SESSION['show_recovery_codes'] = $recoveryCodes;
        redirect('/admin-settings.php?tab=security');
    }

    if ($action === 'disable_totp' && Auth::hasRole('moderator')) {
        if (!post('confirm_disable')) {
            flash('error', 'Please confirm to disable 2FA.');
            redirect('/admin-settings.php');
        }

        Database::run('UPDATE admin_users SET totp_enabled = 0, totp_secret = NULL, recovery_codes = NULL WHERE id = ?', [$adminId]);
        Audit::log($adminId, 'disable_2fa', 'admin', $adminId);
        flash('success', '2FA disabled.');
        redirect('/admin-settings.php');
    }

    if ($action === 'revoke_sessions' && Auth::hasRole('moderator')) {
        Database::run('UPDATE admin_sessions SET revoked = 1 WHERE admin_user_id = ? AND id != ?', [$adminId, session_id()]);
        flash('success', 'All other sessions have been revoked.');
        redirect('/admin-settings.php');
    }
}

// Load current admin data
$currentAdmin = Database::one('SELECT id, name, email, totp_enabled, created_at FROM admin_users WHERE id = ?', [$adminId]);
$activeSessions = Database::all(
    'SELECT id, ip_address, user_agent, last_activity, created_at FROM admin_sessions WHERE admin_user_id = ? ORDER BY last_activity DESC',
    [$adminId]
);

$tab = (string) query('tab', 'profile');
$pageTitle = 'Admin Settings';
$activeNav = 'settings';
require __DIR__ . '/../app/partials/header.php';
?>

<style>
.tabs { display: flex; gap: 12px; border-bottom: 1px solid var(--line); margin-bottom: 20px; flex-wrap: wrap; }
.tab { padding: 12px 16px; border: none; background: transparent; cursor: pointer; color: var(--muted); font-weight: 600; font-size: 14px; border-bottom: 3px solid transparent; }
.tab.active { color: var(--blue); border-bottom-color: var(--blue); }
.tab-content { display: none; }
.tab-content.active { display: block; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-weight: 600; margin-bottom: 6px; color: var(--ink); font-size: 14px; }
.form-group input, .form-group textarea { width: 100%; padding: 10px 12px; border: 1px solid var(--line); border-radius: 8px; font-family: inherit; font-size: 14px; }
.form-group input:focus, .form-group textarea:focus { outline: none; border-color: var(--blue); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.recovery-codes { background: var(--faint); padding: 16px; border-radius: 8px; margin-top: 12px; font-family: monospace; font-size: 13px; line-height: 1.8; }
.recovery-codes code { display: block; margin: 4px 0; }
.copy-btn { display: inline-block; padding: 8px 12px; background: var(--blue); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; }
.copy-btn:hover { opacity: 0.9; }
.totp-secret { background: var(--white); border: 2px solid var(--line); padding: 12px; border-radius: 8px; font-family: monospace; text-align: center; font-size: 16px; letter-spacing: 2px; margin: 12px 0; }
.session-row { padding: 12px; border: 1px solid var(--line); border-radius: 8px; margin-bottom: 8px; background: var(--white); }
.session-info { font-size: 13px; color: var(--muted); }
</style>

<div class="tabs">
    <button class="tab <?= $tab === 'profile' ? 'active' : '' ?>" data-tab="profile">Profile</button>
    <button class="tab <?= $tab === 'security' ? 'active' : '' ?>" data-tab="security">Security</button>
    <button class="tab <?= $tab === 'sessions' ? 'active' : '' ?>" data-tab="sessions">Sessions</button>
</div>

<!-- Profile Tab -->
<div id="profile-tab" class="tab-content <?= $tab === 'profile' ? 'active' : '' ?>">
    <div class="panel">
        <div class="panel-head"><h2>Edit Profile</h2></div>
        <form method="post" class="panel-body">
            <?= Security::csrfField() ?>
            <input type="hidden" name="action" value="update_profile">

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" value="<?= h($currentAdmin['name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?= h($currentAdmin['email'] ?? '') ?>" required>
            </div>

            <button type="submit" class="btn blue">Save profile</button>
        </form>
    </div>
</div>

<!-- Security Tab -->
<div id="security-tab" class="tab-content <?= $tab === 'security' ? 'active' : '' ?>">
    <!-- Change Password Section -->
    <div class="panel">
        <div class="panel-head"><h2>Change Password</h2></div>
        <form method="post" class="panel-body">
            <?= Security::csrfField() ?>
            <input type="hidden" name="action" value="change_password">

            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>New Password (min 12 chars)</label>
                    <input type="password" name="new_password" minlength="12" required>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" minlength="12" required>
                </div>
            </div>

            <button type="submit" class="btn blue">Update password</button>
        </form>
    </div>

    <!-- 2FA Section -->
    <div class="panel">
        <div class="panel-head">
            <h2>Two-Factor Authentication (2FA)</h2>
            <span class="pill <?= ($currentAdmin['totp_enabled'] ?? false) ? 'green' : 'gray' ?>">
                <?= ($currentAdmin['totp_enabled'] ?? false) ? 'Enabled ✓' : 'Disabled' ?>
            </span>
        </div>
        <div class="panel-body">
            <?php if ($currentAdmin['totp_enabled'] ?? false): ?>
                <!-- 2FA is enabled -->
                <p style="color: var(--muted); margin-bottom: 16px;">
                    ✓ Two-factor authentication is active. You'll need an authenticator app code to sign in.
                </p>

                <form method="post" onsubmit="return confirm('Are you sure? This will disable 2FA and make your account less secure.')">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="disable_totp">
                    <input type="hidden" name="confirm_disable" value="1">
                    <button type="submit" class="btn danger">Disable 2FA</button>
                </form>

            <?php else: ?>
                <!-- 2FA is disabled - offer to enable -->
                <?php if (isset($_SESSION['pending_totp_secret'])): ?>
                    <!-- Setup in progress -->
                    <p style="color: var(--muted); margin-bottom: 16px;">
                        Scan this QR code with your authenticator app (Google Authenticator, Authy, Microsoft Authenticator, etc.)
                    </p>

                    <div style="text-align: center; margin: 20px 0;">
                        <p style="font-size: 12px; color: var(--muted); margin-bottom: 12px;">Or enter this code manually:</p>
                        <div class="totp-secret"><?= h($_SESSION['pending_totp_secret']) ?></div>
                        <button type="button" class="copy-btn" onclick="copyToClipboard('<?= h($_SESSION['pending_totp_secret']) ?>')">Copy code</button>
                    </div>

                    <form method="post">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="action" value="verify_totp">
                        <div class="form-group">
                            <label>Enter 6-digit code from your authenticator app</label>
                            <input type="text" name="totp_code" pattern="\d{6}" placeholder="000000" required maxlength="6" style="font-size: 18px; text-align: center; letter-spacing: 4px;">
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button type="submit" class="btn blue">Verify and enable 2FA</button>
                            <a href="/admin-settings.php?tab=security" class="btn" style="text-decoration: none;">Cancel</a>
                        </div>
                    </form>

                    <?php if (isset($_SESSION['show_recovery_codes'])): ?>
                        <div style="background: #fff8f0; border: 1px solid #ffd9b3; border-radius: 8px; padding: 16px; margin-top: 20px;">
                            <h3 style="color: var(--ink); margin-bottom: 12px;">🔑 Save your recovery codes</h3>
                            <p style="color: var(--muted); margin-bottom: 12px; font-size: 13px;">
                                If you lose access to your authenticator app, use these codes to sign in. Each code can only be used once.
                            </p>
                            <div class="recovery-codes" id="recovery-codes">
                                <?php foreach ($_SESSION['show_recovery_codes'] as $code): ?>
                                    <code><?= h($code) ?></code>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="copy-btn" onclick="copyToClipboard(document.getElementById('recovery-codes').innerText)">Copy all codes</button>
                            <a href="/admin-settings.php?tab=security" class="btn" style="margin-left: 8px; text-decoration: none;">I've saved the codes</a>
                            <?php unset($_SESSION['show_recovery_codes']); ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- No setup in progress -->
                    <p style="color: var(--muted); margin-bottom: 16px;">
                        Protect your admin account with two-factor authentication using an authenticator app.
                    </p>

                    <form method="post">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="action" value="setup_totp">
                        <button type="submit" class="btn blue">Enable 2FA</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Sessions Tab -->
<div id="sessions-tab" class="tab-content <?= $tab === 'sessions' ? 'active' : '' ?>">
    <div class="panel">
        <div class="panel-head">
            <h2>Active Sessions</h2>
            <span style="font-size: 13px; color: var(--muted);"><?= count($activeSessions) ?> active</span>
        </div>
        <div class="panel-body">
            <?php foreach ($activeSessions as $session): ?>
                <div class="session-row">
                    <div style="margin-bottom: 6px;">
                        <strong><?= h(mb_substr($session['user_agent'] ?? 'Unknown', 0, 80)) ?></strong>
                        <?php if ($session['id'] === session_id()): ?>
                            <span class="pill blue">Current session</span>
                        <?php endif; ?>
                    </div>
                    <div class="session-info">
                        <strong>IP:</strong> <?= h($session['ip_address']) ?><br>
                        <strong>Started:</strong> <?= h(time_ago($session['created_at'])) ?><br>
                        <strong>Last activity:</strong> <?= h(time_ago(date('Y-m-d H:i:s', (int) $session['last_activity']))) ?>
                    </div>
                </div>
            <?php endforeach; if (!$activeSessions): ?>
                <p style="color: var(--muted); font-size: 13px;">No active sessions.</p>
            <?php endif; ?>

            <?php if (count($activeSessions) > 1): ?>
                <form method="post" style="margin-top: 20px;" onsubmit="return confirm('Sign out from all other devices?')">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="revoke_sessions">
                    <button type="submit" class="btn">Sign out all other sessions</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const tabName = this.getAttribute('data-tab');
        
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        
        // Remove active class from all tabs
        document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));
        
        // Show selected tab content
        const tabContent = document.getElementById(tabName + '-tab');
        if (tabContent) {
            tabContent.classList.add('active');
            this.classList.add('active');
        }
        
        // Update URL without page reload
        window.history.replaceState({}, '', '/admin-settings.php?tab=' + tabName);
    });
});

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard!');
    }).catch(() => {
        alert('Failed to copy. Please try again.');
    });
}
</script>

<?php require __DIR__ . '/../app/partials/footer.php'; ?>
