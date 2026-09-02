<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$admin = Auth::require('moderator');

$pageTitle = 'Analytics';
$activeNav = 'analytics';
require __DIR__ . '/../app/partials/header.php';

// Hourly message data
$hourlyData = Analytics::messagesPerHour();
$hourlyJson = json_encode(array_map(fn($h) => ['hour' => substr($h['hour'], 11, 5), 'count' => $h['count']], array_reverse($hourlyData)));

// Activity breakdown
$topUsers = Database::all(
    "SELECT u.name, u.email, COUNT(m.id) as count FROM users u LEFT JOIN messages m ON m.user_id = u.id 
     WHERE u.deleted_at IS NULL GROUP BY u.id ORDER BY count DESC LIMIT 15"
);

// Conversation health
$convStats = Database::one(
    "SELECT COUNT(*) as total, 
            SUM(CASE WHEN created_at >= datetime('now', '-7 days') THEN 1 ELSE 0 END) as week_new,
            SUM(CASE WHEN type = 'group' THEN 1 ELSE 0 END) as groups,
            SUM(CASE WHEN is_locked = 1 THEN 1 ELSE 0 END) as locked
     FROM conversations"
);

// User growth (last 30 days)
$userGrowth = Database::all(
    "SELECT date(created_at) as day, COUNT(*) as new_users FROM users 
     WHERE created_at >= datetime('now', '-30 days') AND deleted_at IS NULL
     GROUP BY date(created_at) ORDER BY day DESC"
);

// Call statistics
$callStats = Database::one(
    "SELECT COUNT(*) as total, 
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
            COUNT(CASE WHEN status = 'ended' THEN 1 END) as ended,
            AVG(CAST((julianday(ended_at) - julianday(started_at)) * 86400 AS INTEGER)) as avg_duration
     FROM calls"
);
?>

<style>
.chart-container { background: var(--white); border: 1px solid var(--line); border-radius: 10px; padding: 16px; margin-bottom: 16px; }
.chart-title { font-size: 14px; font-weight: 600; margin-bottom: 12px; color: var(--ink); }
.simple-chart { height: 200px; display: flex; align-items: flex-end; gap: 4px; }
.chart-bar { flex: 1; background: var(--blue); border-radius: 4px 4px 0 0; min-height: 2px; position: relative; }
.chart-bar:hover::before { content: attr(data-val); position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); background: var(--ink); color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; white-space: nowrap; }
.stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin-bottom: 16px; }
.stat-box { padding: 12px; background: var(--faint); border-radius: 8px; }
.stat-num { display: block; font-size: 22px; font-weight: 700; color: var(--blue); }
.stat-label { display: block; font-size: 11px; color: var(--muted); text-transform: uppercase; }
</style>

<h2 style="margin-bottom:20px;">Analytics & Reports</h2>

<div class="stats-row">
    <div class="stat-box">
        <span class="stat-num"><?= number_format((int) $convStats['total']) ?></span>
        <span class="stat-label">Conversations total</span>
    </div>
    <div class="stat-box">
        <span class="stat-num"><?= number_format((int) $convStats['week_new']) ?></span>
        <span class="stat-label">Created (7d)</span>
    </div>
    <div class="stat-box">
        <span class="stat-num"><?= number_format((int) $convStats['groups']) ?></span>
        <span class="stat-label">Group chats</span>
    </div>
    <div class="stat-box">
        <span class="stat-num"><?= number_format((int) $convStats['locked']) ?></span>
        <span class="stat-label">Locked</span>
    </div>
    <div class="stat-box">
        <span class="stat-num"><?= number_format((int) $callStats['total']) ?></span>
        <span class="stat-label">Calls total</span>
    </div>
    <div class="stat-box">
        <span class="stat-num"><?= gmdate('m:ss', (int) $callStats['avg_duration']) ?></span>
        <span class="stat-label">Avg call time</span>
    </div>
</div>

<div class="chart-container">
    <div class="chart-title">Messages per hour (last 24h)</div>
    <div class="simple-chart" id="hourly-chart"></div>
</div>

<div class="panel">
    <div class="panel-head"><h2>User growth (last 30 days)</h2></div>
    <table class="grid">
        <thead><tr><th>Date</th><th>New users</th></tr></thead>
        <tbody>
        <?php foreach ($userGrowth as $g): ?>
            <tr>
                <td><?= h($g['day']) ?></td>
                <td><span class="pill green"><?= (int) $g['new_users'] ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="panel">
    <div class="panel-head"><h2>Top message senders (all time)</h2></div>
    <table class="grid">
        <thead><tr><th>User</th><th>Email</th><th>Messages</th></tr></thead>
        <tbody>
        <?php foreach ($topUsers as $u): ?>
            <tr>
                <td><?= h(LaravelCrypt::displayOrFallback($u['name'], 'Unnamed')) ?></td>
                <td><small><?= h($u['email']) ?></small></td>
                <td><span class="pill"><?= number_format((int) $u['count']) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
const hourlyData = <?= $hourlyJson ?>;
const chart = document.getElementById('hourly-chart');
const max = Math.max(0, ...hourlyData.map(h => h.count), 1);

hourlyData.forEach(h => {
    const bar = document.createElement('div');
    bar.className = 'chart-bar';
    bar.style.height = (h.count / max * 180) + 'px';
    bar.setAttribute('data-val', h.count + ' (' + h.hour + ')');
    chart.appendChild(bar);
});
</script>

<?php require __DIR__ . '/../app/partials/footer.php'; ?>
