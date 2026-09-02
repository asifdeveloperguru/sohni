<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$admin = Auth::require('moderator');

// Real-time stats
$onlineUsers = Analytics::onlineUsersCount();
$totalUsers = Analytics::totalUsersCount();
$newUsers7d = Analytics::newUsersCount();
$unactivated = Analytics::unactivatedUsersCount();

// Message analytics
$msgs24h = Analytics::messagesLast24h();
$msgs7d = Analytics::messagesLast7d();
$totalMsgs = Analytics::totalMessages();
$avgMsgPerUser = Analytics::avgMessagesPerUser();
$msgsPerMinute = Analytics::messagesPerMinuteLast1h();

// Data usage
$totalDataUsage = Analytics::totalDataUsage();
$avgDataPerUser = Analytics::avgDataPerUser();
$largestUser = Analytics::largestDataUser();

// Conversation stats
$activeConvs = Analytics::activeConversationsCount();
$dormantConvs = Analytics::dormantConversationsCount();
$groupConvs = Analytics::groupConversationsCount();
$directConvs = Analytics::directConversationsCount();

// Call analytics
$activeCalls = Analytics::activeCallsCount();
$callsToday = Analytics::callsToday();
$totalCalls = Analytics::totalCallsCount();
$avgCallDuration = Analytics::avgCallDurationSeconds();

// System stats
$totalBanned = Database::scalar('SELECT COUNT(*) FROM users WHERE is_banned = 1 AND deleted_at IS NULL');
$totalSuspended = Database::scalar('SELECT COUNT(*) FROM users WHERE is_suspended = 1 AND deleted_at IS NULL');
$openReports = Database::scalar("SELECT COUNT(*) FROM reports WHERE status = 'open'");

$topUsers = Analytics::topUsersById24h();
$recentActivty = Database::all(
    "SELECT a.*, au.name AS admin_name FROM admin_audit_logs a
     LEFT JOIN admin_users au ON au.id = a.admin_user_id
     ORDER BY a.created_at DESC LIMIT 15"
);

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
require __DIR__ . '/../app/partials/header.php';
?>

<style>
.stat-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 16px; }
.metric { padding: 14px; border: 1px solid var(--line); border-radius: 10px; background: var(--white); }
.metric .val { display: block; font: 700 22px Sora, sans-serif; color: var(--blue); margin-bottom: 2px; }
.metric .lbl { display: block; font-size: 11px; color: var(--faint); font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
.metric.warn .val { color: var(--amber); }
.metric.danger .val { color: var(--danger); }
.metric.green .val { color: var(--green); }
</style>

<h2 style="margin-top:0;margin-bottom:16px;font-size:18px;color:var(--ink);">Real-time activity</h2>
<div class="stat-row">
    <div class="metric green"><span class="val"><?= (int) $onlineUsers ?></span><span class="lbl">Online now</span></div>
    <div class="metric"><span class="val"><?= (int) $totalUsers ?></span><span class="lbl">Total users</span></div>
    <div class="metric green"><span class="val"><?= (int) $newUsers7d ?></span><span class="lbl">New (7d)</span></div>
    <div class="metric <?= $unactivated > 0 ? 'warn' : '' ?>"><span class="val"><?= (int) $unactivated ?></span><span class="lbl">Never logged in</span></div>
    <div class="metric <?= $activeCalls > 0 ? 'green' : '' ?>"><span class="val"><?= (int) $activeCalls ?></span><span class="lbl">Calls active</span></div>
    <div class="metric"><span class="val"><?= (int) $callsToday ?></span><span class="lbl">Calls today</span></div>
</div>

<h2 style="margin-top:20px;margin-bottom:16px;font-size:18px;color:var(--ink);">Message traffic</h2>
<div class="stat-row">
    <div class="metric"><span class="val"><?= number_format((float) $msgsPerMinute) ?></span><span class="lbl">Msgs/min (1h avg)</span></div>
    <div class="metric"><span class="val"><?= number_format($msgs24h) ?></span><span class="lbl">Messages (24h)</span></div>
    <div class="metric"><span class="val"><?= number_format($msgs7d) ?></span><span class="lbl">Messages (7d)</span></div>
    <div class="metric"><span class="val"><?= number_format($totalMsgs) ?></span><span class="lbl">Total messages</span></div>
    <div class="metric"><span class="val"><?= $avgMsgPerUser ?></span><span class="lbl">Avg per user</span></div>
</div>

<h2 style="margin-top:20px;margin-bottom:16px;font-size:18px;color:var(--ink);">Data usage & storage</h2>
<div class="stat-row">
    <div class="metric"><span class="val"><?= h(format_bytes((int) $totalDataUsage)) ?></span><span class="lbl">Total usage</span></div>
    <div class="metric"><span class="val"><?= h(format_bytes($avgDataPerUser)) ?></span><span class="lbl">Avg per user</span></div>
</div>

<?php if ($largestUser): ?>
<div class="panel">
    <div class="panel-body" style="font-size:13px;padding:12px;">
        <strong>Heaviest user:</strong> <a href="/user-view.php?id=<?= (int) $largestUser['id'] ?>"><?= h(LaravelCrypt::displayOrFallback($largestUser['name'], 'Unknown')) ?></a>
        (<?= h(format_bytes((int) $largestUser['data_usage_bytes'])) ?>)
    </div>
</div>
<?php endif; ?>

<h2 style="margin-top:20px;margin-bottom:16px;font-size:18px;color:var(--ink);">Conversations</h2>
<div class="stat-row">
    <div class="metric green"><span class="val"><?= (int) $activeConvs ?></span><span class="lbl">Active</span></div>
    <div class="metric"><span class="val"><?= (int) $dormantConvs ?></span><span class="lbl">Dormant (30d)</span></div>
    <div class="metric"><span class="val"><?= (int) $groupConvs ?></span><span class="lbl">Groups</span></div>
    <div class="metric"><span class="val"><?= (int) $directConvs ?></span><span class="lbl">Direct chats</span></div>
</div>

<h2 style="margin-top:20px;margin-bottom:16px;font-size:18px;color:var(--ink);">Calls</h2>
<div class="stat-row">
    <div class="metric"><span class="val"><?= number_format($totalCalls) ?></span><span class="lbl">Total calls</span></div>
    <div class="metric"><span class="val"><?= gmdate('i:s', $avgCallDuration) ?></span><span class="lbl">Avg duration</span></div>
</div>

<h2 style="margin-top:20px;margin-bottom:16px;font-size:18px;color:var(--ink);">Safety & compliance</h2>
<div class="stat-row">
    <div class="metric <?= $totalBanned > 0 ? 'danger' : '' ?>"><span class="val"><?= (int) $totalBanned ?></span><span class="lbl">Banned</span></div>
    <div class="metric <?= $totalSuspended > 0 ? 'warn' : '' ?>"><span class="val"><?= (int) $totalSuspended ?></span><span class="lbl">Suspended</span></div>
    <div class="metric <?= $openReports > 0 ? 'danger' : '' ?>"><span class="val"><?= (int) $openReports ?></span><span class="lbl">Open reports</span></div>
</div>

<div class="panel" style="margin-top:24px;">
    <div class="panel-head"><h2>Most active users (24h)</h2><a class="btn sm" href="/users.php">View all</a></div>
    <table class="grid">
        <thead><tr><th>User</th><th>Messages</th><th>Email</th></tr></thead>
        <tbody>
        <?php foreach ($topUsers as $u): ?>
            <tr>
                <td><a href="/user-view.php?id=<?= (int) $u['id'] ?>"><?= h(LaravelCrypt::displayOrFallback($u['name'], 'Unknown')) ?></a></td>
                <td><span class="pill blue"><?= (int) $u['msg_count'] ?></span></td>
                <td><?= h($u['email']) ?></td>
            </tr>
        <?php endforeach; if (! $topUsers): ?>
            <tr><td colspan="3" class="empty-row">No activity.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="panel">
    <div class="panel-head"><h2>Recent admin actions</h2><a class="btn sm" href="/audit-log.php">Full log</a></div>
    <table class="grid">
        <thead><tr><th>Admin</th><th>Action</th><th>Target</th><th>Time</th></tr></thead>
        <tbody>
        <?php foreach ($recentActivty as $a): ?>
            <tr>
                <td><?= h($a['admin_name'] ?? 'System') ?></td>
                <td><code class="mono" style="font-size:11px"><?= h($a['action']) ?></code></td>
                <td><?= $a['target_type'] ? h($a['target_type'] . ' #' . $a['target_id']) : '—' ?></td>
                <td><?= h(time_ago($a['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../app/partials/footer.php'; ?>
