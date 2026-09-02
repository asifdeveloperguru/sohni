<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$admin = Auth::require('moderator');

$id = (int) query('id', 0);
$report = Database::one(
    'SELECT r.*, ru.name AS reporter_name, ru.email AS reporter_email, tu.name AS reported_name, tu.email AS reported_email
     FROM reports r JOIN users ru ON ru.id = r.reporter_id LEFT JOIN users tu ON tu.id = r.reported_user_id
     WHERE r.id = ?',
    [$id]
);
if (! $report) {
    http_response_code(404);
    exit('Report not found.');
}

if (is_post()) {
    Security::verifyCsrf();
    $action = (string) post('action', '');
    $validStatuses = ['reviewing', 'resolved', 'dismissed'];

    if ($action === 'set_status' && in_array(post('status'), $validStatuses, true)) {
        $newStatus = (string) post('status');
        Database::run(
            'UPDATE reports SET status = ?, resolved_by = ?, resolved_at = CASE WHEN ? IN ("resolved","dismissed") THEN datetime("now") ELSE NULL END WHERE id = ?',
            [$newStatus, $admin['id'], $newStatus, $id]
        );
        Audit::log($admin['id'], 'update_report_status', 'report', $id, ['status' => $newStatus]);
        flash('success', 'Report status updated.');
    }

    redirect('/report-view.php?id=' . $id);
}

$pageTitle = 'Report #' . $id;
$activeNav = 'reports';
require __DIR__ . '/../app/partials/header.php';
?>

<div class="panel">
    <div class="panel-head">
        <h2>Report details</h2>
        <?php $pillClass = ['open' => 'red', 'reviewing' => 'amber', 'resolved' => 'green', 'dismissed' => 'gray'][$report['status']] ?? 'gray'; ?>
        <span class="pill <?= $pillClass ?>"><?= h(ucfirst($report['status'])) ?></span>
    </div>
    <div class="panel-body">
        <div class="detail-grid">
            <div class="detail-item"><label>Reporter</label><div><a href="/user-view.php?id=<?= (int) $report['reporter_id'] ?>"><?= h(LaravelCrypt::displayOrFallback($report['reporter_name'], 'Unknown')) ?></a></div></div>
            <div class="detail-item"><label>Reported user</label><div><?= $report['reported_user_id'] ? '<a href="/user-view.php?id=' . (int) $report['reported_user_id'] . '">' . h(LaravelCrypt::displayOrFallback($report['reported_name'], 'Unknown')) . '</a>' : '—' ?></div></div>
            <div class="detail-item"><label>Conversation</label><div><?= $report['conversation_id'] ? '<a href="/conversation-view.php?id=' . (int) $report['conversation_id'] . '">#' . (int) $report['conversation_id'] . '</a>' : '—' ?></div></div>
            <div class="detail-item"><label>Reason</label><div><?= h(ucfirst($report['reason'])) ?></div></div>
            <div class="detail-item"><label>Filed</label><div><?= h($report['created_at']) ?></div></div>
        </div>
        <?php if ($report['details']): ?>
            <div style="margin-top:16px;">
                <label style="display:block;color:var(--faint);font-size:11px;font-weight:700;text-transform:uppercase;margin-bottom:6px;">Reporter's notes</label>
                <div style="padding:14px;border:1px solid var(--line);border-radius:10px;background:var(--canvas);font-size:13.5px;"><?= nl2br(h($report['details'])) ?></div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h2>Actions</h2></div>
    <div class="panel-body action-row">
        <form method="post"><?= Security::csrfField() ?><input type="hidden" name="action" value="set_status"><input type="hidden" name="status" value="reviewing">
            <button class="btn" type="submit"><i class="fas fa-magnifying-glass"></i> Mark reviewing</button>
        </form>
        <form method="post"><?= Security::csrfField() ?><input type="hidden" name="action" value="set_status"><input type="hidden" name="status" value="resolved">
            <button class="btn" type="submit"><i class="fas fa-check"></i> Mark resolved</button>
        </form>
        <form method="post"><?= Security::csrfField() ?><input type="hidden" name="action" value="set_status"><input type="hidden" name="status" value="dismissed">
            <button class="btn" type="submit"><i class="fas fa-xmark"></i> Dismiss</button>
        </form>
        <?php if ($report['reported_user_id']): ?>
            <a class="btn danger" href="/user-view.php?id=<?= (int) $report['reported_user_id'] ?>"><i class="fas fa-ban"></i> Go ban this user</a>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../app/partials/footer.php'; ?>
