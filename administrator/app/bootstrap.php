<?php
/**
 * Included at the top of every public/*.php page. Loads config, security headers,
 * the session, and all app classes — nothing renders before this has run.
 */

declare(strict_types=1);

define('ADMIN_APP', true);

$config = require __DIR__ . '/../config.php';

require __DIR__ . '/../app/EnvReader.php';
require __DIR__ . '/../app/Database.php';
require __DIR__ . '/../app/helpers.php';
require __DIR__ . '/../app/LaravelCrypt.php';
require __DIR__ . '/../app/Analytics.php';
require __DIR__ . '/../app/Security.php';
require __DIR__ . '/../app/Totp.php';
require __DIR__ . '/../app/Audit.php';
require __DIR__ . '/../app/RateLimiter.php';
require __DIR__ . '/../app/Auth.php';
require __DIR__ . '/../app/Broadcast.php';

Security::sendHeaders();
Security::startSession($config);
