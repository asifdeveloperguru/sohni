<?php
/**
 * Admin authentication, session identity, and role-based access control.
 * Roles: super_admin > admin > moderator (each level includes the ones below it).
 */

declare(strict_types=1);

if (! defined('ADMIN_APP')) {
    http_response_code(403);
    exit('Direct access forbidden.');
}

final class Auth
{
    private const ROLE_RANK = ['moderator' => 1, 'admin' => 2, 'super_admin' => 3];

    public static function attempt(string $email, string $password): array
    {
        $admin = Database::one('SELECT * FROM admin_users WHERE email = ?', [strtolower(trim($email))]);
        $config = require __DIR__ . '/../config.php';

        if (! $admin) {
            // Constant-time-ish: still hash something so timing doesn't reveal account existence.
            password_verify($password, '$2y$12$invalidsaltinvalidsaltinvalidsaltinvalid');
            return ['ok' => false, 'reason' => 'invalid'];
        }

        if ($admin['status'] !== 'active') {
            return ['ok' => false, 'reason' => 'suspended'];
        }

        if (RateLimiter::isLocked($admin)) {
            return ['ok' => false, 'reason' => 'locked', 'seconds' => RateLimiter::lockedSecondsRemaining($admin)];
        }

        if (! password_verify($password, $admin['password'])) {
            RateLimiter::registerFailure($config, (int) $admin['id'], (int) $admin['failed_logins']);
            return ['ok' => false, 'reason' => 'invalid'];
        }

        if ((bool) $admin['totp_enabled']) {
            $_SESSION['_pending_admin_id'] = (int) $admin['id'];
            return ['ok' => true, 'needs_totp' => true];
        }

        self::completeLogin($admin);
        return ['ok' => true, 'needs_totp' => false];
    }

    public static function verifyTotpStep(string $code): bool
    {
        $pendingId = $_SESSION['_pending_admin_id'] ?? null;
        if (! $pendingId) {
            return false;
        }

        $admin = Database::one('SELECT * FROM admin_users WHERE id = ?', [$pendingId]);
        if (! $admin) {
            return false;
        }

        if (Totp::verify($admin['totp_secret'], $code, (require __DIR__ . '/../config.php')['totp_window'])) {
            unset($_SESSION['_pending_admin_id']);
            self::completeLogin($admin);
            return true;
        }

        // Fall back to a one-time recovery code.
        $recovery = json_decode($admin['recovery_codes'] ?? '[]', true) ?: [];
        $normalized = strtoupper(trim($code));
        if (in_array($normalized, $recovery, true)) {
            $remaining = array_values(array_diff($recovery, [$normalized]));
            Database::run('UPDATE admin_users SET recovery_codes = ? WHERE id = ?', [json_encode($remaining), $admin['id']]);
            unset($_SESSION['_pending_admin_id']);
            self::completeLogin($admin);
            Audit::log((int) $admin['id'], 'used_recovery_code');
            return true;
        }

        return false;
    }

    private static function completeLogin(array $admin): void
    {
        Security::regenerate();
        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['_started_at'] = time();

        RateLimiter::reset((int) $admin['id'], $_SERVER['REMOTE_ADDR'] ?? '');

        Database::run(
            'INSERT INTO admin_sessions (id, admin_user_id, ip_address, user_agent, last_activity, revoked, created_at, updated_at)
             VALUES (?, ?, ?, ?, datetime("now"), 0, datetime("now"), datetime("now"))',
            [session_id(), $admin['id'], $_SERVER['REMOTE_ADDR'] ?? null, substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)]
        );

        Audit::log((int) $admin['id'], 'login');
    }

    public static function logout(): void
    {
        $id = self::id();
        if ($id) {
            Database::run('DELETE FROM admin_sessions WHERE id = ?', [session_id()]);
            Audit::log($id, 'logout');
        }
        Security::destroy(require __DIR__ . '/../config.php');
    }

    public static function id(): ?int
    {
        return $_SESSION['admin_id'] ?? null;
    }

    public static function user(): ?array
    {
        $id = self::id();
        return $id ? Database::one('SELECT * FROM admin_users WHERE id = ?', [$id]) : null;
    }

    public static function role(): ?string
    {
        return $_SESSION['admin_role'] ?? null;
    }

    public static function hasRole(string $minimum): bool
    {
        $role = self::role();
        if (! $role) {
            return false;
        }
        return (self::ROLE_RANK[$role] ?? 0) >= (self::ROLE_RANK[$minimum] ?? 99);
    }

    /** Call at the top of every protected page. */
    public static function require(string $minimumRole = 'moderator'): array
    {
        if (! self::id()) {
            redirect('/login.php');
        }

        // Session might have been revoked remotely from the "active sessions" screen.
        $row = Database::one('SELECT revoked FROM admin_sessions WHERE id = ?', [session_id()]);
        if (! $row || (bool) $row['revoked']) {
            Security::destroy(require __DIR__ . '/../config.php');
            redirect('/login.php?reason=revoked');
        }

        Database::run('UPDATE admin_sessions SET last_activity = datetime("now") WHERE id = ?', [session_id()]);

        if (! self::hasRole($minimumRole)) {
            http_response_code(403);
            exit('You do not have permission to view this page.');
        }

        return self::user();
    }
}
