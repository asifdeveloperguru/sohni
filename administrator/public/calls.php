<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$admin = Auth::require('moderator');

$status = (string) query('status', 'all');
$page = max(1, (int) query('page', 1));
$perPage = 25;

$where = $status === 'all' ? '1=1' : 'c.status = ?';
$params = $status === 'all' ? [] : [$status];

$total = (int) Database::scalar("SELECT COUNT(*) FROM calls c WHERE {$where}", $params);
$calls = Database::all(
    "SELECT c.*, u.name AS host_name,
            (SELECT COUNT(*) FROM call_participants cp WHERE cp.call_id = c.id AND cp.state = 'joined') AS active_count
     FROM calls c JOIN users u ON u.id = c.host_id WHERE {$where} ORDER BY c.created_at DESC " . paginate_clause($page, $perPage),
    $params
);
$totalPages = max(1, (int) ceil($total / $perPage));

$pageTitle = 'Calls';
$activeNav = 'calls';
require __DIR__ . '/../app/partials/header.php';
?>

<div class="toolbar">
    <form method="get">
        <select name="status" onchange="this.form.submit()">
            <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All</option>
            <option value="ringing" <?= $status === 'ringing' ? 'selected' : '' ?>>Ringing</option>
            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="ended" <?= $status === 'ended' ? 'selected' : '' ?>>Ended</option>
        </select>
    </form>
</div>

<div class="panel">
    <table class="grid">
        <thead><tr><th>Room</th><th>Host</th><th>Mode</th><th>Status</th><th>Participants</th><th>Started</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($calls as $c): ?>
            <tr>
                <td><code class="mono"><?= h(substr($c['room_id'], 0, 8)) ?>…</code></td>
                <td><?= h(LaravelCrypt::displayOrFallback($c['host_name'], 'Unknown')) ?></td>
                <td><?= h(ucfirst($c['mode'])) ?></td>
                <td>
                    <?php $pillClass = ['ringing' => 'amber', 'active' => 'green', 'ended' => 'gray'][$c['status']] ?? 'gray'; ?>
                    <span class="pill <?= $pillClass ?>"><?= h(ucfirst($c['status'])) ?></span>
                </td>
                <td><?= (int) $c['active_count'] ?> / <?= (int) $c['max_participants'] ?></td>
                <td><?= h(time_ago($c['started_at'] ?? $c['created_at'])) ?></td>
                <td><a class="btn sm" href="/call-view.php?id=<?= (int) $c['id'] ?>">Inspect</a></td>
            </tr>
        <?php endforeach; if (! $calls): ?>
            <tr><td colspan="7" class="empty-row">No calls yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a class="<?= $p === $page ? 'current' : '' ?>" href="?status=<?= h($status) ?>&page=<?= $p ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../app/partials/footer.php'; ?>
