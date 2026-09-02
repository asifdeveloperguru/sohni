<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$admin = Auth::require('moderator');

$search = trim((string) query('q', ''));
$page = max(1, (int) query('page', 1));
$perPage = 25;

$where = '1=1';
$params = [];
if ($search !== '') {
    $where = '(c.name LIKE ? OR CAST(c.id AS TEXT) = ?)';
    $params = ["%{$search}%", $search];
}

$total = (int) Database::scalar("SELECT COUNT(*) FROM conversations c WHERE {$where}", $params);
$conversations = Database::all(
    "SELECT c.*, (SELECT COUNT(*) FROM messages m WHERE m.conversation_id = c.id) AS message_count,
            (SELECT COUNT(*) FROM conversation_user cu WHERE cu.conversation_id = c.id) AS member_count
     FROM conversations c WHERE {$where} ORDER BY c.updated_at DESC " . paginate_clause($page, $perPage),
    $params
);
$totalPages = max(1, (int) ceil($total / $perPage));

$pageTitle = 'Conversations';
$activeNav = 'conversations';
require __DIR__ . '/../app/partials/header.php';
?>

<div class="toolbar">
    <form method="get" style="display:flex;gap:10px;">
        <input type="search" name="q" placeholder="Search by group name or ID…" value="<?= h($search) ?>">
        <button class="btn" type="submit"><i class="fas fa-search"></i> Search</button>
    </form>
</div>

<div class="panel">
    <div class="panel-head"><h2>All conversations</h2></div>
    <table class="grid">
        <thead><tr><th>ID</th><th>Type</th><th>Members</th><th>Messages</th><th>Status</th><th>Last activity</th></tr></thead>
        <tbody>
        <?php foreach ($conversations as $c): ?>
            <tr>
                <td><a href="/conversation-view.php?id=<?= (int) $c['id'] ?>">#<?= (int) $c['id'] ?></a></td>
                <td><?= h(ucfirst($c['type'])) ?></td>
                <td><?= (int) $c['member_count'] ?></td>
                <td><?= (int) $c['message_count'] ?></td>
                <td><?= $c['is_locked'] ? '<span class="pill red">Locked</span>' : '<span class="pill green">Open</span>' ?></td>
                <td><?= h(time_ago($c['updated_at'])) ?></td>
            </tr>
        <?php endforeach; if (! $conversations): ?>
            <tr><td colspan="6" class="empty-row">No conversations found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a class="<?= $p === $page ? 'current' : '' ?>" href="?q=<?= urlencode($search) ?>&page=<?= $p ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../app/partials/footer.php'; ?>
