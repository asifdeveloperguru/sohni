<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$admin = Auth::require('moderator');

$search = trim((string) query('q', ''));
$filter = (string) query('filter', 'all');
$page = max(1, (int) query('page', 1));
$perPage = 25;

$where = ['deleted_at IS NULL'];
$params = [];

if ($search !== '') {
    $where[] = '(email LIKE ? OR sohni_id LIKE ? OR CAST(id AS TEXT) = ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = $search;
}

if ($filter === 'verified') $where[] = 'email_verified_at IS NOT NULL';
if ($filter === 'unverified') $where[] = 'email_verified_at IS NULL';
if ($filter === 'banned') $where[] = 'is_banned = 1';
if ($filter === 'suspended') $where[] = 'is_suspended = 1';

$whereSql = implode(' AND ', $where);
$total = (int) Database::scalar("SELECT COUNT(*) FROM users WHERE {$whereSql}", $params);
$users = Database::all(
    "SELECT id, name, email, sohni_id, sohni_id_type, email_verified_at, is_banned, is_suspended, last_seen_at, created_at
     FROM users WHERE {$whereSql} ORDER BY created_at DESC " . paginate_clause($page, $perPage),
    $params
);

// A search term with zero SQL hits might still match an encrypted display name —
// fall back to a bounded decrypt-and-scan since names cannot be searched in SQL.
if ($search !== '' && $total === 0) {
    $candidates = Database::all('SELECT id, name, email, sohni_id, sohni_id_type, email_verified_at, is_banned, is_suspended, last_seen_at, created_at FROM users WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT 1000');
    $needle = mb_strtolower($search);
    $users = array_values(array_filter($candidates, function ($u) use ($needle) {
        return str_contains(mb_strtolower(LaravelCrypt::displayOrFallback($u['name'], '')), $needle);
    }));
    $total = count($users);
    $users = array_slice($users, ($page - 1) * $perPage, $perPage);
}

$totalPages = max(1, (int) ceil($total / $perPage));

$pageTitle = 'Users';
$activeNav = 'users';
require __DIR__ . '/../app/partials/header.php';
?>

<div class="toolbar">
    <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;">
        <input type="search" name="q" placeholder="Search by email, Sohni ID, or name…" value="<?= h($search) ?>">
        <select name="filter" onchange="this.form.submit()">
            <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All users</option>
            <option value="verified" <?= $filter === 'verified' ? 'selected' : '' ?>>Verified</option>
            <option value="unverified" <?= $filter === 'unverified' ? 'selected' : '' ?>>Unverified</option>
            <option value="suspended" <?= $filter === 'suspended' ? 'selected' : '' ?>>Suspended</option>
            <option value="banned" <?= $filter === 'banned' ? 'selected' : '' ?>>Banned</option>
        </select>
        <button class="btn" type="submit"><i class="fas fa-search"></i> Search</button>
    </form>
</div>

<div class="panel">
    <table class="grid">
        <thead><tr><th>Name</th><th>Email</th><th>Sohni ID</th><th>Status</th><th>Last seen</th><th>Joined</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><a href="/user-view.php?id=<?= (int) $u['id'] ?>"><?= h(LaravelCrypt::displayOrFallback($u['name'], 'Unnamed')) ?></a></td>
                <td><?= h($u['email']) ?></td>
                <td><code class="mono"><?= h($u['sohni_id'] ?? '—') ?></code> <?= $u['sohni_id_type'] === 'premium' ? '<span class="pill blue">Premium</span>' : '' ?></td>
                <td>
                    <?php if ($u['is_banned']): ?><span class="pill red">Banned</span>
                    <?php elseif ($u['is_suspended']): ?><span class="pill amber">Suspended</span>
                    <?php elseif ($u['email_verified_at']): ?><span class="pill green">Verified</span>
                    <?php else: ?><span class="pill gray">Unverified</span><?php endif; ?>
                </td>
                <td><?= h(time_ago($u['last_seen_at'])) ?></td>
                <td><?= h(time_ago($u['created_at'])) ?></td>
            </tr>
        <?php endforeach; if (! $users): ?>
            <tr><td colspan="6" class="empty-row">No users match this search.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a class="<?= $p === $page ? 'current' : '' ?>" href="?q=<?= urlencode($search) ?>&filter=<?= h($filter) ?>&page=<?= $p ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../app/partials/footer.php'; ?>
