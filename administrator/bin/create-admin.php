#!/usr/bin/env php
<?php
/**
 * CLI-only tool to create the first super_admin account.
 * Deliberately not exposed over HTTP — admin registration must never be a public form.
 *
 * Usage:
 *   php bin/create-admin.php "Full Name" admin@example.com
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script can only be run from the command line.');
}

define('ADMIN_APP', true);

require __DIR__ . '/../app/Database.php';
require __DIR__ . '/../app/helpers.php';

$name = $argv[1] ?? null;
$email = $argv[2] ?? null;

if (! $name || ! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Usage: php bin/create-admin.php \"Full Name\" admin@example.com\n");
    exit(1);
}

$existing = Database::one('SELECT id FROM admin_users WHERE email = ?', [strtolower($email)]);
if ($existing) {
    fwrite(STDERR, "An admin with that email already exists.\n");
    exit(1);
}

// Generate a strong random password rather than accepting one via argv (shell history risk).
$password = bin2hex(random_bytes(9));
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

Database::run(
    'INSERT INTO admin_users (name, email, password, role, status, failed_logins, created_at, updated_at)
     VALUES (?, ?, ?, "super_admin", "active", 0, datetime("now"), datetime("now"))',
    [$name, strtolower($email), $hash]
);

echo "Super admin created.\n";
echo "Email:    {$email}\n";
echo "Password: {$password}\n";
echo "Store this password securely — it will not be shown again. Change it after first login.\n";
