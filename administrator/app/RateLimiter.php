<?php
/**
 * Brute-force protection for the login form: counts failures per admin account
 * and locks the account out with an increasing cool-down.
 */

declare(strict_types=1);

if (! defined('ADMIN_APP')) {
    http_response_code(403);
    exit('Direct access forbidden.');
}

final class RateLimiter
{
    public static function isLocked(array $admin): bool
    {
        return ! empty($admin['locked_until']) && strtotime($admin['locked_until']) > time();
    }

    public static function lockedSecondsRemaining(array $admin): int
    {
        if (empty($admin['locked_until'])) {
            return 0;
        }
        return max(0, strtotime($admin['locked_until']) - time());
    }

    public static function registerFailure(array $config, int $adminId, int $currentFailures): void
    {
        $failures = $currentFailures + 1;
        $lockedUntil = null;

        if ($failures >= $config['max_failed_logins']) {
            $lockedUntil = date('Y-m-d H:i:s', time() + $config['lockout_minutes'] * 60);
        }

        Database::run(
            'UPDATE admin_users SET failed_logins = ?, locked_until = ? WHERE id = ?',
            [$failures, $lockedUntil, $adminId]
        );
    }

    public static function reset(int $adminId, string $ip): void
    {
        Database::run(
            'UPDATE admin_users SET failed_logins = 0, locked_until = NULL, last_login_ip = ?, last_login_at = datetime("now") WHERE id = ?',
            [$ip, $adminId]
        );
    }
}
