<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$admin = Auth::require('moderator');

$search = $_GET['q'] ?? '';
$filter = $_GET['f'] ?? 'all'; // all, verified, banned, suspended, never_logged_in, inactive
$page = (int) ($_GET['p'] ?? 1);
$limit = 50;
$offset = ($page - 1) * $limit;

$where = 'WHERE u.deleted_at IS NULL';
$params = [];

if ($search) {
    $search = trim($search);
    $where .= ' AND (u.email LIKE ? OR u.id = ? OR u.sohni_id = ?)';
    $params = ["%$search%", (int) $search ?: 0, $search];
}

match ($filter) {
    'verified' => $where .= ' AND u.email_verified_at IS NOT NULL',
    'unverified' => $where .= ' AND u.email_verified_at IS NULL',
    'banned' => $where .= ' AND u.is_banned = 1',
    'suspended' => $where .= ' AND u.is_suspended = 1',
    'never_logged_in' => $where .= ' AND u.first_login_at IS NULL',
    'inactive' => $where .= ' AND u.last_seen_at < datetime("now", "-30 days")',
    default => null,
};

$count = Database::scalar("SELECT COUNT(*) FROM users u $where", $params);
$users = Database::all(
    "SELECT u.id, u.name, u.email, u.sohni_id, u.is_banned, u.is_suspended, u.login_count, 
            u.email_verified_at, u.created_at, u.last_seen_at, COUNT(m.id) as msg_count
     FROM users u LEFT JOIN messages m ON m.user_id = u.id AND m.created_at >= datetime('now', '-7 days')
     $where GROUP BY u.id ORDER BY u.created_at DESC LIMIT $limit OFFSET $offset",
    $params
);

$pageTitle = "Users";
$activeNav = 'users';
require __DIR__ . '/../app/partials/header.php';
?>

<style>
.filter-tabs { display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; }
.filter-tabs a { padding: 8px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; color: var(--muted); text-decoration: none; border: 1px solid transparent; }
.filter-tabs a.active { background: var(--blue); color: white; border-color: var(--blue); }
.filter-tabs a:hover { border-color: var(--line); }
.bulk-actions { background: var(--faint); padding: 12px; border-radius: 8px; margin-bottom: 16px; display: none; gap: 8px; }
.bulk-actions.show { display: flex; }
.user-row.selected { background: var(--faint); }
.user-row td { padding: 12px 8px; }
</style>

<h2 style="margin-bottom:16px;">Users</h2>

<form method="get" class="search-form" style="margin-bottom:16px;">
    <input type="hidden" name="f" value="<?= h($filter) ?>">
    <input type="text" name="q" placeholder="Search by email, ID, or Sohni ID…" value="<?= h($search) ?>" style="flex:1;">
    <button type="submit" class="btn">Search</button>
</form>

<div class="filter-tabs">
    <a href="?f=all" class="<?= $filter === 'all' ? 'active' : '' ?>">All (<?= number_format($count) ?>)</a>
    <a href="?f=never_logged_in" class="<?= $filter === 'never_logged_in' ? 'active' : '' ?>">Never logged in</a>
    <a href="?f=inactive" class="<?= $filter === 'inactive' ? 'active' : '' ?>">Inactive 30d+</a>
    <a href="?f=unverified" class="<?= $filter === 'unverified' ? 'active' : '' ?>">Email unverified</a>
    <a href="?f=suspended" class="<?= $filter === 'suspended' ? 'active' : '' ?>">Suspended</a>
    <a href="?f=banned" class="<?= $filter === 'banned' ? 'active' : '' ?>">Banned</a>
</div>

<div class="bulk-actions" id="bulk-actions">
    <button type="button" class="btn" onclick="bulkAction('verify')">✓ Verify selected</button>
    <button type="button" class="btn warn" onclick="bulkAction('suspend')">⏸ Suspend 7d</button>
    <button type="button" class="btn danger" onclick="bulkAction('ban')">🚫 Ban selected</button>
    <button type="button" class="btn" onclick="bulkAction('export')">⬇️ Export CSV</button>
    <span style="margin-left:auto;font-size:12px;color:var(--muted);" id="bulk-count">0 selected</span>
</div>

<form id="user-list-form">
    <table class="grid">
        <thead>
            <tr>
                <th style="width:24px;"><input type="checkbox" id="select-all" onchange="toggleAll()"></th>
                <th>Name</th>
                <th>Email</th>
                <th>Login count</th>
                <th>Messages (7d)</th>
                <th>Status</th>
                <th>Joined</th>
                <th>Last seen</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="user-tbody">
        <?php foreach ($users as $u): ?>
            <tr class="user-row">
                <td><input type="checkbox" class="user-cb" value="<?= (int) $u['id'] ?>" onchange="updateBulkCount()"></td>
                <td><a href="/user-view.php?id=<?= (int) $u['id'] ?>"><?= h(LaravelCrypt::displayOrFallback($u['name'], 'Unnamed')) ?></a></td>
                <td><small><?= h($u['email']) ?></small></td>
                <td><span class="pill"><?= (int) $u['login_count'] ?></span></td>
                <td><span class="pill"><?= (int) $u['msg_count'] ?></span></td>
                <td>
                    <?php if ($u['is_banned']): ?>
                        <span class="pill danger">Banned</span>
                    <?php elseif ($u['is_suspended']): ?>
                        <span class="pill warn">Suspended</span>
                    <?php elseif (!$u['email_verified_at']): ?>
                        <span class="pill muted">Unverified</span>
                    <?php else: ?>
                        <span class="pill green">Active</span>
                    <?php endif; ?>
                </td>
                <td><small><?= h(time_ago($u['created_at'])) ?></small></td>
                <td><small><?= h($u['last_seen_at'] ? time_ago($u['last_seen_at']) : '—') ?></small></td>
                <td><a class="btn xs" href="/user-view.php?id=<?= (int) $u['id'] ?>">→</a></td>
            </tr>
        <?php endforeach; if (!$users): ?>
            <tr><td colspan="9" class="empty-row">No users match this filter.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</form>

<?php if ($count > $limit): ?>
<div style="text-align:center;margin-top:20px;color:var(--muted);font-size:13px;">
    <?php
    $totalPages = ceil($count / $limit);
    echo "Page $page of " . number_format($totalPages) . " • ";
    if ($page > 1): ?>
        <a href="?f=<?= h($filter) ?>&q=<?= h($search) ?>&p=<?= $page - 1 ?>">← Prev</a>
    <?php endif; ?>
    <?php if ($page < $totalPages): ?>
        <a href="?f=<?= h($filter) ?>&q=<?= h($search) ?>&p=<?= $page + 1 ?>">Next →</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<script>
function toggleAll() {
    const checked = document.getElementById('select-all').checked;
    document.querySelectorAll('.user-cb').forEach(cb => {
        cb.checked = checked;
        cb.closest('.user-row').classList.toggle('selected', checked);
    });
    updateBulkCount();
}

function updateBulkCount() {
    const selected = document.querySelectorAll('.user-cb:checked').length;
    document.getElementById('bulk-actions').classList.toggle('show', selected > 0);
    document.getElementById('bulk-count').textContent = selected + ' selected';
}

function getSelectedIds() {
    return Array.from(document.querySelectorAll('.user-cb:checked')).map(cb => parseInt(cb.value));
}

function bulkAction(action) {
    const ids = getSelectedIds();
    if (ids.length === 0) { alert('No users selected.'); return; }
    if (action === 'export') {
        window.location.href = '/api/users-export.php?ids=' + ids.join(',');
        return;
    }
    if (! confirm('Perform action on ' + ids.length + ' user(s)?')) return;
    
    // POST to bulk action handler
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/api/bulk-user-action.php';
    form.innerHTML = '<input type="hidden" name="action" value="' + action + '">'
        + '<input type="hidden" name="ids" value="' + ids.join(',') + '">';
    document.body.appendChild(form);
    form.submit();
}

// Check row when clicking checkbox
document.querySelectorAll('.user-cb').forEach(cb => {
    cb.addEventListener('change', function() {
        this.closest('.user-row').classList.toggle('selected', this.checked);
    });
});
</script>

<?php require __DIR__ . '/../app/partials/footer.php'; ?>
