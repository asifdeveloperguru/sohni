<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$admin = Auth::require('admin');

$config = require __DIR__ . '/../config.php';
$env = admin_read_frontend_env();

function port_is_open(string $host, int $port, float $timeout = 1.5): bool
{
    $conn = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if ($conn) { fclose($conn); return true; }
    return false;
}

$dbWritable = is_writable($config['db_path']);
$dbSize = file_exists($config['db_path']) ? filesize($config['db_path']) : 0;

$reverbHost = $env['REVERB_HOST'] ?? '127.0.0.1';
$reverbPort = (int) ($env['REVERB_PORT'] ?? 8080);
$reverbUp = port_is_open($reverbHost, $reverbPort);

$uploadsPath = $config['chat_uploads_path'];
$uploadsSize = 0;
if ($uploadsPath && is_dir($uploadsPath)) {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($uploadsPath, FilesystemIterator::SKIP_DOTS)) as $file) {
        $uploadsSize += $file->getSize();
    }
}

$failedJobs = (int) Database::scalar('SELECT COUNT(*) FROM failed_jobs');
$pendingJobs = (int) Database::scalar('SELECT COUNT(*) FROM jobs');
$activeAdminSessions = (int) Database::scalar('SELECT COUNT(*) FROM admin_sessions WHERE revoked = 0');

$pageTitle = 'System health';
$activeNav = 'health';
require __DIR__ . '/../app/partials/header.php';
?>

<div class="stat-grid">
    <div class="stat-card <?= $dbWritable ? '' : 'danger' ?>"><span class="num"><?= $dbWritable ? 'OK' : 'FAIL' ?></span><span class="lbl">Database writable</span></div>
    <div class="stat-card"><span class="num"><?= h(format_bytes($dbSize)) ?></span><span class="lbl">Database size</span></div>
    <div class="stat-card <?= $reverbUp ? '' : 'danger' ?>"><span class="num"><?= $reverbUp ? 'UP' : 'DOWN' ?></span><span class="lbl">Reverb (real-time) server</span></div>
    <div class="stat-card"><span class="num"><?= h(format_bytes($uploadsSize)) ?></span><span class="lbl">Encrypted media storage</span></div>
    <div class="stat-card <?= $failedJobs > 0 ? 'warn' : '' ?>"><span class="num"><?= $failedJobs ?></span><span class="lbl">Failed queue jobs</span></div>
    <div class="stat-card"><span class="num"><?= $pendingJobs ?></span><span class="lbl">Pending queue jobs</span></div>
    <div class="stat-card"><span class="num"><?= $activeAdminSessions ?></span><span class="lbl">Active admin sessions</span></div>
    <div class="stat-card"><span class="num"><?= h(PHP_VERSION) ?></span><span class="lbl">PHP version (this panel)</span></div>
</div>

<div class="panel">
    <div class="panel-head"><h2>Notes</h2></div>
    <div class="panel-body" style="font-size:13.5px;color:var(--muted);line-height:1.7;">
        <p><strong>Reverb DOWN</strong> means chat presence, call signaling, and incoming-call notifications will fail
        with a connection error until <code class="mono">php artisan reverb:start</code> is running.</p>
        <p><strong>Failed queue jobs</strong> above zero usually means an email or background task is silently not
        completing — worth checking <code class="mono">frontend/storage/logs/laravel.log</code>.</p>
    </div>
</div>

<?php require __DIR__ . '/../app/partials/footer.php'; ?>
