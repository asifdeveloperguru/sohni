<?php
/**
 * Central configuration for the Sohni Administrator panel.
 *
 * This app is intentionally a separate, dependency-free PHP codebase that talks
 * directly to the same SQLite database as the main Laravel app. Keeping it isolated
 * means a vulnerability in one codebase does not automatically expose the other.
 */

declare(strict_types=1);

if (! defined('ADMIN_APP')) {
    http_response_code(403);
    exit('Direct access forbidden.');
}

return [
    // Same database the Laravel app uses — single source of truth, no data duplication.
    'db_path' => realpath(__DIR__ . '/../frontend/database/database.sqlite') ?: __DIR__ . '/../frontend/database/database.sqlite',

    // Separate cookie name so admin sessions can never collide with end-user sessions.
    'session_name' => 'sohni_admin_sid',
    'session_lifetime_minutes' => 30,      // idle timeout
    'session_absolute_hours' => 8,         // hard cap regardless of activity

    'app_name' => 'Sohni Administrator',

    // Login brute-force protection.
    'max_failed_logins' => 5,
    'lockout_minutes' => 15,

    // TOTP 2FA.
    'totp_issuer' => 'Sohni Admin',
    'totp_window' => 1, // accept 1 step (±30s) of clock drift

    // Storage paths this app is allowed to read from the main app.
    'chat_uploads_path' => realpath(__DIR__ . '/../frontend/storage/app/private/chat-media') ?: null,

    // Force HTTPS-only cookies when the panel itself is served over TLS.
    'force_secure_cookie' => (($_SERVER['HTTPS'] ?? '') !== '') || (($_SERVER['SERVER_PORT'] ?? '') === '443'),
];
