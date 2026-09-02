<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$admin = Auth::require('super_admin');

if (is_post()) {
    Security::verifyCsrf();
    $action = (string) post('action', '');

    if ($action === 'create') {
        $name = trim((string) post('name', ''));
        $email = strtolower(trim((string) post('email', '')));
        $role = in_array(post('role'), ['moderator', 'admin', 'super_admin'], true) ? post('role') : 'moderator';

        if ($name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Enter a valid name and email.');
        } elseif (Database::one('SELECT id FROM admin_users WHERE email = ?', [$email])) {
            flash('error', 'An admin with that email already exists.');
        } else {
            $password = bin2hex(random_bytes(9));
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            Database::run(
                'INSERT INTO admin_users (name, email, password, role, status, failed_logins, created_at, updated_at) VALUES (?, ?, ?, ?, "active", 0, datetime("now"), datetime("now"))',
                [$name, $email, $hash, $role]
            );
            $newId = (int) Database::lastInsertId();
            Audit::log($admin['id'], 'create_admin', 'admin_user', $newId, ['role' => $role]);
            flash('success', "Admin created. Temporary password: {$password} — share this securely and ask them to change it immediately.");
        }
    }

    redirect('/admins.php');
}

$admins = Database::all('SELECT id, name, email, role, status, totp_enabled, last_login_at, last_login_ip, created_at FROM admin_users ORDER BY created_at ASC');

$pageTitle = 'Admin accounts';
$activeNav = 'admins';
require __DIR__ . '/../app/partials/header.php';
?>

<div class="panel">
    <div class="panel-head"><h2>Create admin account</h2></div>
    <div class="panel-body">
        <form method="post" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <?= Security::csrfField() ?><input type="hidden" name="action" value="create">
            <div class="field" style="margin:0;"><label>Name</label><input type="text" name="name" required></div>
            <div class="field" style="margin:0;"><label>Email</label><input type="email" name="email" required></div>
            <div class="field" style="margin:0;">
                <label>Role</label>
                <select name="role">
                    <option value="moderator">Moderator</option>
                    <option value="admin">Admin</option>
                    <option value="super_admin">Super admin</option>
                </select>
            </div>
            <button class="btn primary" type="submit"><i class="fas fa-user-plus"></i> Create</button>
        </form>
        <p class="muted-note" style="text-align:left;margin-top:12px;">A random temporary password is generated and shown once. The new admin should change it and enable 2FA on first login.</p>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h2>All admins</h2></div>
    <table class="grid">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>2FA</th><th>Last login</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($admins as $a): ?>
            <tr>
                <td><?= h($a['name']) ?><?= (int) $a['id'] === (int) $admin['id'] ? ' <span class="pill blue">You</span>' : '' ?></td>
                <td><?= h($a['email']) ?></td>
                <td><?= h(str_replace('_', ' ', ucfirst($a['role']))) ?></td>
                <td><?= $a['status'] === 'active' ? '<span class="pill green">Active</span>' : '<span class="pill red">Suspended</span>' ?></td>
                <td><?= $a['totp_enabled'] ? '<span class="pill green">Enabled</span>' : '<span class="pill gray">Off</span>' ?></td>
                <td><?= h(time_ago($a['last_login_at'])) ?> <?= $a['last_login_ip'] ? '(' . h($a['last_login_ip']) . ')' : '' ?></td>
                <td><a class="btn sm" href="/admin-view.php?id=<?= (int) $a['id'] ?>">Manage</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../app/partials/footer.php'; ?>
