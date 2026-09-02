<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$admin = Auth::require('moderator');

$status = (string) query('status', 'open');
$page = max(1, (int) query('page', 1));
$perPage = 25;

$where = $status === 'all' ? '1=1' : 'r.status = ?';
$params = $status === 'all' ? [] : [$status];

$total = (int) Database::scalar("SELECT COUNT(*) FROM reports r WHERE {$where}", $params);
$reports = Database::all(
    "SELECT r.*, ru.name AS reporter_name, tu.name AS reported_name
     FROM reports r
     JOIN users ru ON ru.id = r.reporter_id
     LEFT JOIN users tu ON tu.id = r.reported_user_id
     WHERE {$where} ORDER BY r.created_at DESC " . paginate_clause($page, $perPage),
    $params
);
$totalPages = max(1, (int) ceil($total / $perPage));

$pageTitle = 'Reports';
$activeNav = 'reports';
require __DIR__ . '/../app/partials/header.php';
?>

<div class="toolbar">
    <form method="get">
        <select name="status" onchange="this.form.submit()">
            <option value="open" <?= $status === 'open' ? 'selected' : '' ?>>Open</option>
            <option value="reviewing" <?= $status === 'reviewing' ? 'selected' : '' ?>>Reviewing</option>
            <option value="resolved" <?= $status === 'resolved' ? 'selected' : '' ?>>Resolved</option>
            <option value="dismissed" <?= $status === 'dismissed' ? 'selected' : '' ?>>Dismissed</option>
            <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All</option>
        </select>
    </form>
</div>

<div class="panel">
    <table class="grid">
        <thead><tr><th>Reporter</th><th>Reported user</th><th>Reason</th><th>Status</th><th>Filed</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($reports as $r): ?>
            <tr>
                <td><?= h(LaravelCrypt::displayOrFallback($r['reporter_name'], 'Unknown')) ?></td>
                <td><?= $r['reported_user_id'] ? h(LaravelCrypt::displayOrFallback($r['reported_name'], 'Unknown')) : '—' ?></td>
                <td><?= h(ucfirst($r['reason'])) ?></td>
                <td>
                    <?php $pillClass = ['open' => 'red', 'reviewing' => 'amber', 'resolved' => 'green', 'dismissed' => 'gray'][$r['status']] ?? 'gray'; ?>
                    <span class="pill <?= $pillClass ?>"><?= h(ucfirst($r['status'])) ?></span>
                </td>
                <td><?= h(time_ago($r['created_at'])) ?></td>
                <td><a class="btn sm" href="/report-view.php?id=<?= (int) $r['id'] ?>">Review</a></td>
            </tr>
        <?php endforeach; if (! $reports): ?>
            <tr><td colspan="6" class="empty-row">No reports here.</td></tr>
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
