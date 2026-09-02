<?php
/**
 * Persistent, immutable trail of every state-changing admin action.
 * Never expose a delete/edit path for this table in the UI.
 */

declare(strict_types=1);

if (! defined('ADMIN_APP')) {
    http_response_code(403);
    exit('Direct access forbidden.');
}

final class Audit
{
    public static function log(?int $adminId, string $action, ?string $targetType = null, ?int $targetId = null, array $meta = []): void
    {
        Database::run(
            'INSERT INTO admin_audit_logs (admin_user_id, action, target_type, target_id, meta, ip_address, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, datetime("now"), datetime("now"))',
            [
                $adminId,
                $action,
                $targetType,
                $targetId,
                $meta ? json_encode($meta) : null,
                $_SERVER['REMOTE_ADDR'] ?? null,
            ]
        );
    }
}
