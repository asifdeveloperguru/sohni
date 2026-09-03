<?php

declare(strict_types=1);

namespace Sohni\Scripts;

use RuntimeException;

function isAbsolutePath(string $path): bool
{
    return str_starts_with($path, DIRECTORY_SEPARATOR)
        || str_starts_with($path, '\\')
        || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1;
}

function sqliteDatabasePath(string $basePath, ?string $database = null): string
{
    $database ??= 'storage/database/database.sqlite';

    if ($database === ':memory:' || isAbsolutePath($database)) {
        return $database;
    }

    return rtrim($basePath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $database);
}

function ensureDirectory(string $path): void
{
    if (is_dir($path)) {
        @chmod($path, 0775);

        return;
    }

    if (! mkdir($path, 0775, true) && ! is_dir($path)) {
        throw new RuntimeException(sprintf('Unable to create directory [%s].', $path));
    }

    @chmod($path, 0775);
}

function updateEnvironmentFile(string $envFile, array $values): void
{
    $lines = file_exists($envFile) ? file($envFile, FILE_IGNORE_NEW_LINES) : [];
    $updated = [];
    $seen = [];

    foreach ($lines as $line) {
        $replaced = false;

        foreach ($values as $key => $value) {
            if (str_starts_with($line, $key.'=')) {
                $updated[] = $key.'='.$value;
                $seen[$key] = true;
                $replaced = true;
                break;
            }
        }

        if (! $replaced) {
            $updated[] = $line;
        }
    }

    foreach ($values as $key => $value) {
        if (! isset($seen[$key])) {
            $updated[] = $key.'='.$value;
        }
    }

    file_put_contents($envFile, implode(PHP_EOL, $updated).PHP_EOL);
}

function prepareSqliteDeployment(string $basePath, ?string $envFile = null): string
{
    $basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
    $envFile ??= $basePath.DIRECTORY_SEPARATOR.'.env';
    $envExample = $basePath.DIRECTORY_SEPARATOR.'.env.example';
    $databasePath = sqliteDatabasePath($basePath);
    $directories = [
        dirname($databasePath),
        $basePath.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'logs',
        $basePath.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'cache',
        $basePath.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'sessions',
        $basePath.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'views',
        $basePath.DIRECTORY_SEPARATOR.'bootstrap'.DIRECTORY_SEPARATOR.'cache',
    ];

    foreach ($directories as $directory) {
        ensureDirectory($directory);
    }

    if (! file_exists($databasePath) && ! touch($databasePath)) {
        throw new RuntimeException(sprintf('Unable to create SQLite database [%s].', $databasePath));
    }

    @chmod($databasePath, 0664);

    if (! file_exists($envFile) && file_exists($envExample)) {
        copy($envExample, $envFile);
    }

    updateEnvironmentFile($envFile, [
        'DB_CONNECTION' => 'sqlite',
        'DB_DATABASE' => $databasePath,
    ]);

    return $databasePath;
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $databasePath = prepareSqliteDeployment(dirname(__DIR__));

    echo 'SQLite database ready: '.$databasePath.PHP_EOL;
}
