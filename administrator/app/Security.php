<?php
/**
 * Security headers, session hardening, and CSRF token handling.
 * Applied on every request via bootstrap.php before any page logic runs.
 */

declare(strict_types=1);

if (! defined('ADMIN_APP')) {
    http_response_code(403);
    exit('Direct access forbidden.');
}

final class Security
{
    public static function sendHeaders(): void
    {
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: no-referrer');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(), display-capture=()');
        header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; script-src 'self' 'unsafe-inline'; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
        header('Cache-Control: no-store, no-cache, must-revalidate, private');
        header('X-Admin-Panel: sohni');
    }

    public static function startSession(array $config): void
    {
        session_name($config['session_name']);
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Strict',
            'secure' => $config['force_secure_cookie'],
        ]);
        ini_set('session.use_strict_mode', '1');
        session_start();

        // Absolute session lifetime, independent of idle timeout.
        if (! isset($_SESSION['_started_at'])) {
            $_SESSION['_started_at'] = time();
        } elseif (time() - $_SESSION['_started_at'] > $config['session_absolute_hours'] * 3600) {
            self::destroy($config);
            redirect('/login.php?reason=expired');
        }

        // Idle timeout.
        if (isset($_SESSION['_last_activity']) && time() - $_SESSION['_last_activity'] > $config['session_lifetime_minutes'] * 60) {
            self::destroy($config);
            redirect('/login.php?reason=idle');
        }
        $_SESSION['_last_activity'] = time();
    }

    public static function destroy(array $config): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            setcookie($config['session_name'], '', time() - 42000, '/');
        }
        session_destroy();
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function csrfToken(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="_csrf" value="' . h(self::csrfToken()) . '">';
    }

    public static function verifyCsrf(): void
    {
        $token = (string) post('_csrf', '');
        if ($token === '' || empty($_SESSION['_csrf']) || ! hash_equals($_SESSION['_csrf'], $token)) {
            http_response_code(419);
            exit('Session expired or invalid request token. Go back and try again.');
        }
    }
}
