<?php
/**
 * Shared page chrome: sidebar nav + topbar. Include after Auth::require() so
 * $admin is available, and set $pageTitle / $activeNav before including.
 */

declare(strict_types=1);
if (! defined('ADMIN_APP')) { http_response_code(403); exit; }

$navItems = [
    ['key' => 'dashboard', 'href' => '/index.php', 'icon' => 'fa-gauge', 'label' => 'Dashboard'],
    ['key' => 'analytics', 'href' => '/analytics.php', 'icon' => 'fa-chart-line', 'label' => 'Analytics'],
    ['key' => 'users', 'href' => '/users-v2.php', 'icon' => 'fa-users', 'label' => 'Users'],
    ['key' => 'conversations', 'href' => '/conversations.php', 'icon' => 'fa-comments', 'label' => 'Conversations'],
    ['key' => 'reports', 'href' => '/reports.php', 'icon' => 'fa-flag', 'label' => 'Reports'],
    ['key' => 'calls', 'href' => '/calls.php', 'icon' => 'fa-phone', 'label' => 'Calls'],
];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($pageTitle ?? 'Sohni Administrator') ?></title>
<link rel="icon" href="data:,">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="/assets/admin.css">
</head>
<body>
<div class="shell">
    <aside class="sidebar">
        <div class="sidebar-brand"><i class="fas fa-shield-halved"></i><span>Sohni Admin</span></div>
        <nav>
            <?php foreach ($navItems as $item): ?>
                <a class="nav-link <?= ($activeNav ?? '') === $item['key'] ? 'active' : '' ?>" href="<?= h($item['href']) ?>">
                    <i class="fas <?= h($item['icon']) ?>"></i><span><?= h($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
            <?php if (Auth::hasRole('admin')): ?>
                <div class="nav-section">Administration</div>
                <a class="nav-link <?= ($activeNav ?? '') === 'audit' ? 'active' : '' ?>" href="/audit-log.php"><i class="fas fa-clock-rotate-left"></i><span>Audit log</span></a>
                <a class="nav-link <?= ($activeNav ?? '') === 'health' ? 'active' : '' ?>" href="/health.php"><i class="fas fa-heart-pulse"></i><span>System health</span></a>
                <?php if (Auth::hasRole('super_admin')): ?>
                    <a class="nav-link <?= ($activeNav ?? '') === 'admins' ? 'active' : '' ?>" href="/admins.php"><i class="fas fa-user-shield"></i><span>Admin accounts</span></a>
                <?php endif; ?>
            <?php endif; ?>
            <div class="nav-section">Account</div>
            <a class="nav-link <?= ($activeNav ?? '') === 'settings' ? 'active' : '' ?>" href="/admin-settings.php"><i class="fas fa-cog"></i><span>My settings</span></a>
        </nav>
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <span class="avatar-dot"><?= h(strtoupper(substr($admin['name'] ?? '?', 0, 1))) ?></span>
                <div>
                    <strong><?= h($admin['name'] ?? '') ?></strong>
                    <span><?= h($admin['role'] ?? '') ?></span>
                </div>
            </div>
            <form method="post" action="/logout.php" style="margin-top:8px">
                <?= Security::csrfField() ?>
                <button class="btn sm block" type="submit"><i class="fas fa-right-from-bracket"></i> Sign out</button>
            </form>
        </div>
    </aside>
    <div class="main">
        <header class="topbar">
            <h1><?= h($pageTitle ?? '') ?></h1>
            <?= $topbarExtra ?? '' ?>
        </header>
        <div class="content">
            <?php foreach (take_flashes() as $f): ?>
                <div class="alert <?= h($f['type']) ?>"><?= h($f['message']) ?></div>
            <?php endforeach; ?>
