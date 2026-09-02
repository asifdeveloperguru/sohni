<?php
/**
 * Minimal .env reader for the frontend app — used only to read Reverb broadcast
 * credentials so the admin panel can publish real-time events (e.g. force-ending
 * a call) without depending on Laravel's framework.
 */

declare(strict_types=1);

if (! defined('ADMIN_APP')) {
    http_response_code(403);
    exit('Direct access forbidden.');
}

function admin_read_frontend_env(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $path = __DIR__ . '/../frontend/.env';
    $values = [];

    if (is_readable($path)) {
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $value = trim($value, "\"'");
            $values[$key] = $value;
        }
    }

    return $cache = $values;
}
