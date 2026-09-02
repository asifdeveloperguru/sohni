<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$admin = Auth::require('admin');

$page = max(1, (int) query('page', 1));
$perPage = 40;
$actionFilter = trim((string) query('action', ''));

$where = '1=1';
$params = [];
if ($actionFilter !== '') {
    $where = 'a.action LIKE ?';
    $params[] = "%{$actionFilter}%";
}

$total = (int) Database::scalar("SELECT COUNT(*) FROM admin_audit_logs a WHERE {$where}", $params);
$logs = Database::all(
    "SELECT a.*, au.name AS admin_name FROM admin_audit_logs a LEFT JOIN admin_users au ON au.id = a.admin_user_id
     WHERE {$where} ORDER BY a.created_at DESC " . paginate_clause($page, $perPage),
    $params
);
$totalPages = max(1, (int) ceil($total / $perPage));

$pageTitle = 'Audit log';
$activeNav = 'audit';
require __DIR__ . '/../app/partials/header.php';
?>

<div class="toolbar">
    <form method="get">
        <input type="search" name="action" placeholder="Filter by action (e.g. ban_user)…" value="<?= h($actionFilter) ?>">
        <button class="btn" type="submit"><i class="fas fa-filter"></i> Filter</button>
    </form>
</div>

<div class="panel">
    <div class="panel-head"><h2>Every state-changing admin action, permanently recorded</h2></div>
    <table class="grid">
        <thead><tr><th>Admin</th><th>Action</th><th>Target</th><th>IP</th><th>Details</th><th>When</th></tr></thead>
        <tbody>
        <?php foreach ($logs as $l): $meta = json_decode($l['meta'] ?? '[]', true) ?: []; ?>
            <tr>
                <td><?= h($l['admin_name'] ?? 'System') ?></td>
                <td><code class="mono"><?= h($l['action']) ?></code></td>
                <td><?= $l['target_type'] ? h($l['target_type'] . ' #' . $l['target_id']) : '—' ?></td>
                <td><?= h($l['ip_address'] ?? '—') ?></td>
                <td><?= $meta ? h(json_encode($meta)) : '—' ?></td>
                <td><?= h($l['created_at']) ?></td>
            </tr>
        <?php endforeach; if (! $logs): ?>
            <tr><td colspan="6" class="empty-row">No activity recorded yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a class="<?= $p === $page ? 'current' : '' ?>" href="?action=<?= urlencode($actionFilter) ?>&page=<?= $p ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../app/partials/footer.php'; ?>
