<?php
/**
 * Reimplements Laravel's Encrypter::decryptString() so this standalone panel can
 * read the app's AES-256-CBC "encrypted" Eloquent casts (name, phone, address, etc.)
 * using the same APP_KEY. This is encryption-at-rest for PII, not E2E — the app
 * (and therefore a trusted admin tool holding APP_KEY) is expected to read it.
 * Message/call content is a different, genuine E2E scheme this panel cannot decrypt.
 */

declare(strict_types=1);

if (! defined('ADMIN_APP')) {
    http_response_code(403);
    exit('Direct access forbidden.');
}

final class LaravelCrypt
{
    private static ?string $key = null;

    private static function key(): ?string
    {
        if (self::$key !== null) {
            return self::$key;
        }

        $env = admin_read_frontend_env();
        $appKey = $env['APP_KEY'] ?? '';

        if (str_starts_with($appKey, 'base64:')) {
            $appKey = base64_decode(substr($appKey, 7));
        }

        return self::$key = $appKey ?: null;
    }

    /** Mirrors Encrypter::decryptString() — no PHP serialize wrapper, cipher aes-256-cbc. */
    public static function decrypt(?string $payload): ?string
    {
        if (! $payload) {
            return null;
        }

        $key = self::key();
        if (! $key) {
            return null;
        }

        try {
            $json = json_decode(base64_decode($payload), true);
            if (! is_array($json) || ! isset($json['iv'], $json['value'], $json['mac'])) {
                return null;
            }

            $calculatedMac = hash_hmac('sha256', $json['iv'] . $json['value'], $key);
            if (! hash_equals($calculatedMac, $json['mac'])) {
                return null;
            }

            $iv = base64_decode($json['iv']);
            $decrypted = openssl_decrypt($json['value'], 'aes-256-cbc', $key, 0, $iv);

            return $decrypted === false ? null : $decrypted;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Best-effort display helper: falls back to a placeholder instead of raw ciphertext. */
    public static function displayOrFallback(?string $payload, string $fallback = '—'): string
    {
        $value = self::decrypt($payload);
        return $value === null || $value === '' ? $fallback : $value;
    }
}
