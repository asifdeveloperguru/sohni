<?php

declare(strict_types=1);

$basePath = dirname(__DIR__);
$envPath = $basePath . '/.env';
$exampleEnvPath = $basePath . '/.env.example';

if (! is_file($envPath) && is_file($exampleEnvPath)) {
    copy($exampleEnvPath, $envPath);
    echo "Created .env from .env.example\n";
}

$env = readEnvFile($envPath);

if (($env['APP_KEY'] ?? '') === '') {
    upsertEnvValues($envPath, [
        'APP_KEY' => 'base64:' . base64_encode(random_bytes(32)),
    ]);
    echo "Generated APP_KEY\n";
    $env = readEnvFile($envPath);
}

foreach ([
    'storage/logs',
    'storage/framework/cache',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/app/public',
    'storage/app/private',
    'storage/app/private/chat-media',
    'storage/app/private/chat-uploads',
    'bootstrap/cache',
    'database',
] as $relativePath) {
    ensureDirectory($basePath . '/' . $relativePath);
}

if (($env['DB_CONNECTION'] ?? 'sqlite') === 'sqlite') {
    $databasePath = $env['DB_DATABASE'] ?? 'database/database.sqlite';
    $databasePath = str_starts_with($databasePath, '/')
        ? $databasePath
        : $basePath . '/' . ltrim($databasePath, './');

    ensureDirectory(dirname($databasePath));

    if (! is_file($databasePath)) {
        touch($databasePath);
        echo "Created SQLite database: {$databasePath}\n";
    }
}

$publicStoragePath = $basePath . '/public/storage';
$storageTargetPath = $basePath . '/storage/app/public';

if (! file_exists($publicStoragePath)) {
    @symlink($storageTargetPath, $publicStoragePath);

    if (is_link($publicStoragePath) || is_dir($publicStoragePath)) {
        echo "Prepared public storage link\n";
    } else {
        echo "Warning: could not create public/storage symlink\n";
    }
}

foreach ([
    $basePath . '/storage',
    $basePath . '/bootstrap/cache',
    $basePath . '/database',
] as $path) {
    @chmod($path, 0775);
}

if (($env['BROADCAST_CONNECTION'] ?? 'log') === 'reverb') {
    $missing = array_filter([
        'REVERB_APP_ID',
        'REVERB_APP_KEY',
        'REVERB_APP_SECRET',
        'REVERB_HOST',
    ], static fn (string $key): bool => ($env[$key] ?? '') === '');

    if ($missing !== []) {
        echo 'Warning: missing Reverb variables: ' . implode(', ', $missing) . "\n";
    }
}

echo "Laravel Cloud deployment bootstrap complete\n";

function ensureDirectory(string $path): void
{
    if (! is_dir($path)) {
        mkdir($path, 0775, true);
    }
}

function readEnvFile(string $path): array
{
    if (! is_file($path)) {
        return [];
    }

    $values = [];

    foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        $trimmed = trim($line);

        if ($trimmed === '' || str_starts_with($trimmed, '#') || ! str_contains($trimmed, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $values[trim($key)] = trim($value);
    }

    return $values;
}

function upsertEnvValues(string $path, array $updates): void
{
    $lines = is_file($path) ? (file($path, FILE_IGNORE_NEW_LINES) ?: []) : [];
    $handled = [];

    foreach ($lines as &$line) {
        if (! str_contains($line, '=')) {
            continue;
        }

        [$key] = explode('=', $line, 2);
        $key = trim($key);

        if (! array_key_exists($key, $updates)) {
            continue;
        }

        $line = $key . '=' . $updates[$key];
        $handled[$key] = true;
    }
    unset($line);

    foreach ($updates as $key => $value) {
        if (! isset($handled[$key])) {
            $lines[] = $key . '=' . $value;
        }
    }

    file_put_contents($path, implode(PHP_EOL, $lines) . PHP_EOL);
}
