<?php
/**
 * Publishes events to Laravel Reverb using the Pusher wire protocol, so the
 * admin panel can (e.g.) force-end a live call without needing Laravel loaded.
 * Reverb speaks this exact protocol — same one the frontend's broadcast() calls use.
 */

declare(strict_types=1);

if (! defined('ADMIN_APP')) {
    http_response_code(403);
    exit('Direct access forbidden.');
}

final class Broadcast
{
    public static function publish(string $channel, string $event, array $data): bool
    {
        $env = admin_read_frontend_env();

        $appId = $env['REVERB_APP_ID'] ?? null;
        $key = $env['REVERB_APP_KEY'] ?? null;
        $secret = $env['REVERB_APP_SECRET'] ?? null;
        $host = $env['REVERB_HOST'] ?? '127.0.0.1';
        $port = (int) ($env['REVERB_PORT'] ?? 8080);
        $scheme = ($env['REVERB_SCHEME'] ?? 'http') === 'https' ? 'https' : 'http';

        if (! $appId || ! $key || ! $secret) {
            return false;
        }

        $body = json_encode([
            'name' => $event,
            'channel' => $channel,
            'data' => json_encode($data),
        ]);

        $path = "/apps/{$appId}/events";
        $authTimestamp = time();
        $bodyMd5 = md5($body);

        $params = [
            'auth_key' => $key,
            'auth_timestamp' => $authTimestamp,
            'auth_version' => '1.0',
            'body_md5' => $bodyMd5,
        ];
        ksort($params);

        $stringToSign = "POST\n{$path}\n" . http_build_query($params);
        $signature = hash_hmac('sha256', $stringToSign, $secret);
        $params['auth_signature'] = $signature;

        $url = "{$scheme}://{$host}:{$port}{$path}?" . http_build_query($params);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 3,
            CURLOPT_CONNECTTIMEOUT => 2,
        ]);
        curl_exec($ch);
        $ok = curl_errno($ch) === 0;
        curl_close($ch);

        return $ok;
    }
}
