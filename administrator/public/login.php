<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';

if (Auth::id()) {
    redirect('/index.php');
}

$error = null;
$needsTotp = isset($_SESSION['_pending_admin_id']);

if (is_post()) {
    Security::verifyCsrf();

    if (isset($_POST['totp_code'])) {
        if (Auth::verifyTotpStep((string) post('totp_code', ''))) {
            redirect('/index.php');
        }
        $error = 'That code was not accepted. Check your authenticator app or use a recovery code.';
        $needsTotp = true;
    } else {
        $result = Auth::attempt((string) post('email', ''), (string) post('password', ''));

        if ($result['ok'] && ($result['needs_totp'] ?? false)) {
            $needsTotp = true;
        } elseif ($result['ok']) {
            redirect('/index.php');
        } else {
            $error = match ($result['reason'] ?? '') {
                'locked' => 'Too many failed attempts. Try again in ' . ceil(($result['seconds'] ?? 0) / 60) . ' minute(s).',
                'suspended' => 'This admin account has been suspended.',
                default => 'Invalid email or password.',
            };
        }
    }
}

$reason = query('reason');
$reasonMessage = match ($reason) {
    'expired' => 'Your session expired after 8 hours. Please sign in again.',
    'idle' => 'You were signed out after a period of inactivity.',
    'revoked' => 'This session was revoked from another device.',
    default => null,
};
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign in — Sohni Administrator</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="/assets/admin.css">
</head>
<body>
<div class="auth-shell">
    <div class="auth-card">
        <div class="auth-brand"><i class="fas fa-shield-halved"></i> Sohni Administrator</div>

        <?php if ($reasonMessage): ?><div class="alert info"><?= h($reasonMessage) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>

        <?php if ($needsTotp): ?>
            <h1>Two-factor verification</h1>
            <p class="sub">Enter the 6-digit code from your authenticator app.</p>
            <form method="post">
                <?= Security::csrfField() ?>
                <div class="field">
                    <label for="totp_code">Authentication code</label>
                    <input type="text" id="totp_code" name="totp_code" inputmode="numeric" autocomplete="one-time-code" maxlength="20" autofocus required>
                </div>
                <button class="btn primary block" type="submit">Verify</button>
            </form>
            <p class="muted-note">Lost your device? Use one of your recovery codes instead.</p>
        <?php else: ?>
            <h1>Sign in</h1>
            <p class="sub">Restricted access. All activity is logged.</p>
            <form method="post">
                <?= Security::csrfField() ?>
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" autocomplete="username" required autofocus>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" autocomplete="current-password" required>
                </div>
                <button class="btn primary block" type="submit">Sign in</button>
            </form>
        <?php endif; ?>

        <p class="muted-note">This panel is separate from the Sohni app and does not share your user account.</p>
    </div>
</div>
</body>
</html>
