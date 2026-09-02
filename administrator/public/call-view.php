<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$admin = Auth::require('moderator');

$id = (int) query('id', 0);
$call = Database::one('SELECT c.*, u.name AS host_name FROM calls c JOIN users u ON u.id = c.host_id WHERE c.id = ?', [$id]);
if (! $call) {
    http_response_code(404);
    exit('Call not found.');
}

if (is_post()) {
    Security::verifyCsrf();
    $action = (string) post('action', '');

    if ($action === 'force_end' && Auth::hasRole('admin') && $call['status'] !== 'ended') {
        Database::run('UPDATE calls SET status = "ended", ended_at = datetime("now") WHERE id = ?', [$id]);
        Database::run('UPDATE call_participants SET state = "left", left_at = datetime("now") WHERE call_id = ? AND state = "joined"', [$id]);

        $published = Broadcast::publish('presence-call.' . $call['room_id'], 'state', ['action' => 'ended', 'data' => []]);

        Audit::log($admin['id'], 'force_end_call', 'call', $id, ['notified_clients' => $published]);
        flash('success', $published ? 'Call ended and participants notified in real time.' : 'Call marked ended (participants will see it end on their next check).');
    }

    redirect('/call-view.php?id=' . $id);
}

$participants = Database::all(
    'SELECT cp.*, u.name, u.email FROM call_participants cp JOIN users u ON u.id = cp.user_id WHERE cp.call_id = ?',
    [$id]
);

$pageTitle = 'Call inspector';
$activeNav = 'calls';
require __DIR__ . '/../app/partials/header.php';
?>

<div class="panel">
    <div class="panel-head">
        <h2>Room <code class="mono"><?= h($call['room_id']) ?></code></h2>
        <?php $pillClass = ['ringing' => 'amber', 'active' => 'green', 'ended' => 'gray'][$call['status']] ?? 'gray'; ?>
        <span class="pill <?= $pillClass ?>"><?= h(ucfirst($call['status'])) ?></span>
    </div>
    <div class="panel-body">
        <div class="detail-grid">
            <div class="detail-item"><label>Host</label><div><a href="/user-view.php?id=<?= (int) $call['host_id'] ?>"><?= h(LaravelCrypt::displayOrFallback($call['host_name'], 'Unknown')) ?></a></div></div>
            <div class="detail-item"><label>Mode</label><div><?= h(ucfirst($call['mode'])) ?></div></div>
            <div class="detail-item"><label>Max participants</label><div><?= (int) $call['max_participants'] ?></div></div>
            <div class="detail-item"><label>Started</label><div><?= h($call['started_at'] ?? '—') ?></div></div>
            <div class="detail-item"><label>Ended</label><div><?= h($call['ended_at'] ?? '—') ?></div></div>
        </div>
        <p class="muted-note" style="text-align:left;margin-top:14px;">
            <i class="fas fa-lock"></i> Call audio/video is peer-to-peer and end-to-end encrypted (WebRTC DTLS-SRTP).
            This server only ever sees signaling metadata — never the media itself.
        </p>
    </div>
</div>

<?php if (Auth::hasRole('admin') && $call['status'] !== 'ended'): ?>
<div class="panel">
    <div class="panel-head"><h2>Actions</h2></div>
    <div class="panel-body">
        <form method="post" onsubmit="return confirm('Force-end this call for everyone?')">
            <?= Security::csrfField() ?><input type="hidden" name="action" value="force_end">
            <button class="btn danger" type="submit"><i class="fas fa-phone-slash"></i> Force end call</button>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="panel">
    <div class="panel-head"><h2>Participants</h2></div>
    <table class="grid">
        <thead><tr><th>Name</th><th>Email</th><th>State</th><th>Joined</th><th>Left</th></tr></thead>
        <tbody>
        <?php foreach ($participants as $p): ?>
            <tr>
                <td><a href="/user-view.php?id=<?= (int) $p['user_id'] ?>"><?= h(LaravelCrypt::displayOrFallback($p['name'], 'Unknown')) ?></a></td>
                <td><?= h($p['email']) ?></td>
                <td><span class="pill <?= $p['state'] === 'joined' ? 'green' : 'gray' ?>"><?= h(ucfirst($p['state'])) ?></span></td>
                <td><?= h($p['joined_at'] ?? '—') ?></td>
                <td><?= h($p['left_at'] ?? '—') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../app/partials/footer.php'; ?>
