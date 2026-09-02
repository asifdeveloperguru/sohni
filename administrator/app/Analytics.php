<?php
/**
 * Analytics queries for the admin panel dashboard.
 * Gathers real-time and historical metrics about user activity, usage, and system load.
 */

declare(strict_types=1);

if (! defined('ADMIN_APP')) {
    http_response_code(403);
    exit('Direct access forbidden.');
}

final class Analytics
{
    /** Current online users (had activity in last 5 minutes). */
    public static function onlineUsersCount(): int
    {
        return (int) Database::scalar(
            "SELECT COUNT(*) FROM users WHERE deleted_at IS NULL AND last_seen_at >= datetime('now', '-5 minutes')"
        );
    }

    /** Total registered users (not deleted). */
    public static function totalUsersCount(): int
    {
        return (int) Database::scalar('SELECT COUNT(*) FROM users WHERE deleted_at IS NULL');
    }

    /** Users who have never logged in. */
    public static function unactivatedUsersCount(): int
    {
        return (int) Database::scalar('SELECT COUNT(*) FROM users WHERE deleted_at IS NULL AND first_login_at IS NULL');
    }

    /** Messages sent in the last 24 hours. */
    public static function messagesLast24h(): int
    {
        return (int) Database::scalar("SELECT COUNT(*) FROM messages WHERE created_at >= datetime('now', '-1 day')");
    }

    /** Messages sent in the last 7 days. */
    public static function messagesLast7d(): int
    {
        return (int) Database::scalar("SELECT COUNT(*) FROM messages WHERE created_at >= datetime('now', '-7 days')");
    }

    /** Total cumulative messages. */
    public static function totalMessages(): int
    {
        return (int) Database::scalar('SELECT COUNT(*) FROM messages');
    }

    /** Average messages per user. */
    public static function avgMessagesPerUser(): float
    {
        $total = (int) Database::scalar('SELECT COUNT(*) FROM messages');
        $users = self::totalUsersCount();
        return $users > 0 ? round($total / $users, 2) : 0;
    }

    /** Total data transferred (sum of file sizes). */
    public static function totalDataUsage(): int
    {
        return (int) Database::scalar('SELECT COALESCE(SUM(data_usage_bytes), 0) FROM users WHERE deleted_at IS NULL');
    }

    /** Average data per user. */
    public static function avgDataPerUser(): int
    {
        $total = self::totalDataUsage();
        $users = self::totalUsersCount();
        return $users > 0 ? (int) ($total / $users) : 0;
    }

    /** Largest data user. */
    public static function largestDataUser(): ?array
    {
        return Database::one(
            'SELECT id, name, email, data_usage_bytes FROM users WHERE deleted_at IS NULL ORDER BY data_usage_bytes DESC LIMIT 1'
        );
    }

    /** Active conversations (with at least one message in last 7 days). */
    public static function activeConversationsCount(): int
    {
        return (int) Database::scalar(
            "SELECT COUNT(DISTINCT m.conversation_id) FROM messages m WHERE m.created_at >= datetime('now', '-7 days')"
        );
    }

    /** Dormant conversations (no messages in 30 days). */
    public static function dormantConversationsCount(): int
    {
        return (int) Database::scalar(
            "SELECT COUNT(DISTINCT c.id) FROM conversations c
             WHERE c.id NOT IN (SELECT DISTINCT conversation_id FROM messages WHERE created_at >= datetime('now', '-30 days'))
             AND c.created_at < datetime('now', '-30 days')"
        );
    }

    /** Group conversations (type='group'). */
    public static function groupConversationsCount(): int
    {
        return (int) Database::scalar("SELECT COUNT(*) FROM conversations WHERE type = 'group'");
    }

    /** Direct message conversations. */
    public static function directConversationsCount(): int
    {
        return (int) Database::scalar("SELECT COUNT(*) FROM conversations WHERE type = 'direct'");
    }

    /** Active calls (ringing or in progress). */
    public static function activeCallsCount(): int
    {
        return (int) Database::scalar("SELECT COUNT(*) FROM calls WHERE status IN ('ringing','active')");
    }

    /** Total calls ever made. */
    public static function totalCallsCount(): int
    {
        return (int) Database::scalar('SELECT COUNT(*) FROM calls');
    }

    /** Average call duration in seconds. */
    public static function avgCallDurationSeconds(): int
    {
        $avg = Database::scalar(
            "SELECT AVG(CAST((julianday(ended_at) - julianday(started_at)) * 86400 AS INTEGER))
             FROM calls WHERE ended_at IS NOT NULL"
        );
        return (int) ($avg ?? 0);
    }

    /** Top 10 most active users by message count. */
    public static function topUsersById24h(): array
    {
        return Database::all(
            "SELECT u.id, u.name, u.email, COUNT(m.id) AS msg_count
             FROM users u LEFT JOIN messages m ON m.user_id = u.id AND m.created_at >= datetime('now', '-1 day')
             WHERE u.deleted_at IS NULL GROUP BY u.id ORDER BY msg_count DESC LIMIT 10"
        );
    }

    /** New users (registered in last 7 days). */
    public static function newUsersCount(): int
    {
        return (int) Database::scalar("SELECT COUNT(*) FROM users WHERE deleted_at IS NULL AND created_at >= datetime('now', '-7 days')");
    }

    /** Hourly message breakdown for last 24h. */
    public static function messagesPerHour(): array
    {
        return Database::all(
            "SELECT datetime(created_at, 'start of hour') AS hour,
                    COUNT(*) AS count FROM messages
             WHERE created_at >= datetime('now', '-24 hours')
             GROUP BY datetime(created_at, 'start of hour')
             ORDER BY hour DESC LIMIT 24"
        );
    }

    /** System health: avg messages per minute (last hour). */
    public static function messagesPerMinuteLast1h(): float
    {
        $count = (int) Database::scalar("SELECT COUNT(*) FROM messages WHERE created_at >= datetime('now', '-1 hour')");
        return round($count / 60, 2);
    }

    /** Calls made today. */
    public static function callsToday(): int
    {
        return (int) Database::scalar("SELECT COUNT(*) FROM calls WHERE created_at >= datetime('now', 'start of day')");
    }

    /** Users with 2FA enabled (from admin table). */
    public static function adminsWith2FA(): int
    {
        return (int) Database::scalar('SELECT COUNT(*) FROM admin_users WHERE totp_enabled = 1');
    }

    /** Failed login attempts (sum of failed_logins for all locked admins). */
    public static function totalFailedAdminLogins(): int
    {
        return (int) Database::scalar('SELECT COALESCE(SUM(failed_logins), 0) FROM admin_users');
    }
}
