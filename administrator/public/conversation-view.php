<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$admin = Auth::require('moderator');

$id = (int) query('id', 0);
$conversation = Database::one('SELECT * FROM conversations WHERE id = ?', [$id]);
if (! $conversation) {
    http_response_code(404);
    exit('Conversation not found.');
}

if (is_post()) {
    Security::verifyCsrf();
    $action = (string) post('action', '');

    if ($action === 'toggle_lock' && Auth::hasRole('admin')) {
        $locked = $conversation['is_locked'] ? 0 : 1;
        Database::run('UPDATE conversations SET is_locked = ? WHERE id = ?', [$locked, $id]);
        Audit::log($admin['id'], $locked ? 'lock_conversation' : 'unlock_conversation', 'conversation', $id);
        flash('success', $locked ? 'Conversation locked — no new messages can be sent.' : 'Conversation unlocked.');
    }

    if ($action === 'delete' && Auth::hasRole('admin')) {
        Database::run('DELETE FROM conversations WHERE id = ?', [$id]);
        Audit::log($admin['id'], 'delete_conversation', 'conversation', $id);
        flash('success', 'Conversation and all its messages were deleted.');
        redirect('/conversations.php');
    }

    redirect('/conversation-view.php?id=' . $id);
}

$members = Database::all(
    'SELECT u.id, u.name, u.email, cu.last_read_message_id, cu.created_at AS joined_at
     FROM conversation_user cu JOIN users u ON u.id = cu.user_id WHERE cu.conversation_id = ?',
    [$id]
);

$typeCounts = Database::all('SELECT type, COUNT(*) AS c FROM messages WHERE conversation_id = ? GROUP BY type', [$id]);
$recentMessages = Database::all(
    'SELECT m.*, u.name AS sender_name FROM messages m JOIN users u ON u.id = m.user_id
     WHERE m.conversation_id = ? ORDER BY m.id DESC LIMIT 30',
    [$id]
);

$pageTitle = 'Conversation #' . $id;
$activeNav = 'conversations';
require __DIR__ . '/../app/partials/header.php';
?>

<div class="panel">
    <div class="panel-head">
        <h2>Overview</h2>
        <div class="action-row">
            <?= $conversation['is_locked'] ? '<span class="pill red">Locked</span>' : '<span class="pill green">Open</span>' ?>
        </div>
    </div>
    <div class="panel-body">
        <div class="detail-grid">
            <div class="detail-item"><label>Type</label><div><?= h(ucfirst($conversation['type'])) ?></div></div>
            <div class="detail-item"><label>Group name</label><div><?= h($conversation['name'] ?? '—') ?></div></div>
            <div class="detail-item"><label>Members</label><div><?= count($members) ?></div></div>
            <div class="detail-item"><label>Created</label><div><?= h($conversation['created_at']) ?></div></div>
        </div>
        <p class="muted-note" style="text-align:left;margin-top:14px;">
            <i class="fas fa-lock"></i> Message text and media are end-to-end encrypted. This page shows metadata
            only (sender, type, size, timestamp) — never message content.
        </p>
    </div>
</div>

<?php if (Auth::hasRole('admin')): ?>
<div class="panel">
    <div class="panel-head"><h2>Moderation actions</h2></div>
    <div class="panel-body action-row">
        <form method="post" onsubmit="return confirm('<?= $conversation['is_locked'] ? 'Unlock' : 'Lock' ?> this conversation?')">
            <?= Security::csrfField() ?><input type="hidden" name="action" value="toggle_lock">
            <button class="btn" type="submit"><i class="fas fa-lock"></i> <?= $conversation['is_locked'] ? 'Unlock' : 'Lock' ?> conversation</button>
        </form>
        <form method="post" onsubmit="return confirm('Permanently delete this conversation and all its messages? This cannot be undone.')">
            <?= Security::csrfField() ?><input type="hidden" name="action" value="delete">
            <button class="btn danger" type="submit"><i class="fas fa-trash"></i> Delete conversation</button>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="panel">
    <div class="panel-head"><h2>Members</h2></div>
    <table class="grid">
        <thead><tr><th>Name</th><th>Email</th><th>Joined</th></tr></thead>
        <tbody>
        <?php foreach ($members as $m): ?>
            <tr>
                <td><a href="/user-view.php?id=<?= (int) $m['id'] ?>"><?= h(LaravelCrypt::displayOrFallback($m['name'], 'Unnamed')) ?></a></td>
                <td><?= h($m['email']) ?></td>
                <td><?= h(time_ago($m['joined_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="panel">
    <div class="panel-head"><h2>Message type breakdown</h2></div>
    <div class="panel-body action-row">
        <?php foreach ($typeCounts as $t): ?>
            <span class="pill blue"><?= h(ucfirst($t['type'])) ?>: <?= (int) $t['c'] ?></span>
        <?php endforeach; if (! $typeCounts): ?><span class="empty-row">No messages yet.</span><?php endif; ?>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h2>Recent activity (metadata only)</h2></div>
    <table class="grid">
        <thead><tr><th>Sender</th><th>Type</th><th>Encrypted</th><th>Size</th><th>Sent</th></tr></thead>
        <tbody>
        <?php foreach ($recentMessages as $m): ?>
            <tr>
                <td><?= h(LaravelCrypt::displayOrFallback($m['sender_name'], 'Unknown')) ?></td>
                <td><?= h(ucfirst($m['type'])) ?></td>
                <td><?= $m['is_encrypted'] ? '<span class="pill green">Yes</span>' : '<span class="pill gray">No</span>' ?></td>
                <td><?= h(format_bytes($m['file_size'])) ?></td>
                <td><?= h(time_ago($m['created_at'])) ?></td>
            </tr>
        <?php endforeach; if (! $recentMessages): ?>
            <tr><td colspan="5" class="empty-row">No messages yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../app/partials/footer.php'; ?>
