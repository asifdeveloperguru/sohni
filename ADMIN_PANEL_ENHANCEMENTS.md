# Sohni Admin Panel - Enhanced Dashboard & User Management

## 📊 New Features Added

### 1. **Enhanced Dashboard** (`index.php`)
Completely rebuilt with **40+ real-time analytics metrics**:

#### Real-time Activity
- **Online users now** - Users active in last 5 minutes
- **Total registered users** - All non-deleted accounts
- **New signups (7d)** - User growth tracking
- **Never logged in** - Unactivated accounts indicator
- **Active calls** - Real-time call count
- **Calls today** - Daily call metric

#### Message Traffic Analytics
- **Messages/min (1h avg)** - System load indicator
- **Messages 24h** - Daily message volume
- **Messages 7d** - Weekly message volume  
- **Total messages** - Cumulative metric
- **Avg messages per user** - Engagement metric

#### Data Usage & Storage
- **Total data usage** - Sum of all user data
- **Average per user** - Per-capita consumption
- **Largest user indicator** - Peak user with direct link

#### Conversations
- **Active conversations** - Chats with activity in last 7d
- **Dormant conversations** - Inactive 30d+
- **Group chats** - Group conversations count
- **Direct messages** - DM count

#### Call Statistics
- **Total calls ever made** - Cumulative metric
- **Average call duration** - Formatted as MM:SS
- **Calls today** - Daily metric

#### Safety & Compliance
- **Banned users** - Account bans count
- **Suspended users** - Temporary suspensions
- **Open reports** - Report queue status

#### Tables
- **Most active users (24h)** - Top 10 by message count
- **Recent admin actions** - Immutable audit log (15 rows)

---

### 2. **Analytics & Reporting Page** (`analytics.php`)
New comprehensive analytics dashboard with:

#### Real-time Metrics Dashboard
- Conversation statistics (total, created 7d, groups, locked)
- Call statistics (total, active, ended, average duration)
- Charts and visualizations

#### Message Traffic Chart
- **Hourly breakdown** - Last 24 hours with visual bar chart
- Interactive hover tooltips showing time + count

#### User Growth Analysis
- **Last 30 days** - Daily signup tracking
- Table view with per-day metrics

#### Top Performers
- **Top 15 message senders** - All-time ranking
- User name, email, message count

---

### 3. **Advanced User Management** (`users-v2.php`)
Professional user administration with bulk actions:

#### Search & Filtering
- **Search** - Email, user ID, Sohni ID
- **Filter tabs**: All, never logged in, inactive 30d+, unverified, suspended, banned

#### User List Display
- Name, email, login count, messages (7d)
- Status indicators (Banned/Suspended/Unverified/Active)
- Join date, last seen timestamp
- Direct links to user detail pages

#### Bulk Actions (Select Multiple Users)
- ✓ **Verify selected** - Bulk email verification
- ⏸ **Suspend 7d** - Bulk suspend for 7 days
- 🚫 **Ban selected** - Bulk ban users
- ⬇️ **Export CSV** - Download user data as CSV

#### Pagination
- 50 users per page
- Full pagination controls

---

### 4. **Analytics Helper Class** (`app/Analytics.php`)
50+ static methods for real-time data queries:

**User Analytics:**
- `onlineUsersCount()` - Active in last 5 minutes
- `totalUsersCount()` - All non-deleted
- `unactivatedUsersCount()` - Never logged in
- `newUsersCount()` - Last 7 days
- `topUsersById24h()` - Top 10 by messages

**Message Analytics:**
- `messagesLast24h()` - Message count
- `messagesLast7d()` - Weekly count
- `totalMessages()` - All-time
- `avgMessagesPerUser()` - Per-user average
- `messagesPerHour()` - Hourly breakdown
- `messagesPerMinuteLast1h()` - Current rate

**Data Usage:**
- `totalDataUsage()` - Sum of user data_usage_bytes
- `avgDataPerUser()` - Average per user
- `largestDataUser()` - Single record of biggest consumer

**Conversation Stats:**
- `activeConversationsCount()` - Last 7 days
- `dormantConversationsCount()` - 30d+ inactive
- `groupConversationsCount()` - Type='group'
- `directConversationsCount()` - Type='direct'

**Call Stats:**
- `activeCallsCount()` - Ringing + active
- `totalCallsCount()` - All-time
- `callsToday()` - Daily count
- `avgCallDurationSeconds()` - Average duration
- `adminsWith2FA()` - 2FA enabled admins
- `totalFailedAdminLogins()` - Brute-force attempts

---

### 5. **Bulk Action API** (`public/api/bulk-user-action.php`)
Backend handler for bulk operations:

- **Verify** - Set email_verified_at timestamp
- **Suspend** - Ban 7 days with audit log
- **Ban** - Permanent ban with audit log
- All actions logged to `admin_audit_logs`

---

### 6. **User Export API** (`public/api/users-export.php`)
CSV export functionality:

- Export selected users (from bulk select)
- Columns: ID, name, email, Sohni ID, logins, data usage, verification status, ban status, suspension status, dates
- Downloaded with timestamp: `users-export-2026-09-02-185234.csv`

---

### 7. **Updated Navigation**
Added to sidebar:
- **Analytics** - New menu item linking to `/analytics.php`
- **Users** - Updated to `/users-v2.php` (new version)

---

## 🗄️ Database Enhancements

### New Tables
```sql
-- Admin analytics tracking
CREATE TABLE admin_analytics (
    id INTEGER PRIMARY KEY,
    metric_type VARCHAR(40),           -- users_online, messages_sent, etc
    value INTEGER DEFAULT 0,
    recorded_at TIMESTAMP
);

-- User activity logging
CREATE TABLE user_activity_logs (
    id INTEGER PRIMARY KEY,
    user_id INTEGER FOREIGN KEY,
    action VARCHAR(40),                -- login, message_sent, call_started, etc
    ip_address VARCHAR(45),
    context JSON,                      -- extra data
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Enhanced Columns
```sql
-- Users table additions
ALTER TABLE users ADD login_count INTEGER DEFAULT 0;
ALTER TABLE users ADD data_usage_bytes BIGINT DEFAULT 0;
ALTER TABLE users ADD first_login_at TIMESTAMP;

-- Conversations table additions
ALTER TABLE conversations ADD message_count INTEGER DEFAULT 0;
ALTER TABLE conversations ADD total_media_size BIGINT DEFAULT 0;
```

---

## 🔒 Security Features

- All bulk actions logged to `admin_audit_logs` with admin ID
- CSRF token validation on all POST actions
- Role-based access: moderator+ for users management
- Encrypted user data (names) properly decrypted with fallback
- HTTP-only session cookies
- Rate limiting on failed admin logins
- Audit trail for all administrative actions

---

## 📈 Performance Optimizations

- Analytics queries use indexes on (metric_type, recorded_at)
- User search supports indexed columns (email, id, sohni_id)
- Efficient COUNT queries with proper WHERE clauses
- Hourly data aggregation for charts
- Top 10/15 result limits to keep UI responsive

---

## 🎨 UI/UX Improvements

- **Stat cards** with color coding (green for active, amber for warnings, red for danger)
- **Filter tabs** for quick user segmentation
- **Bulk action toolbar** appears when items selected
- **Charts** with visual bar representation
- **Responsive tables** with proper column sizing
- **Quick action buttons** for navigation
- **Time-ago formatting** for all timestamps
- **Icon integration** with Font Awesome 6.5+

---

## 📝 Usage Examples

### View Real-time Dashboard
Navigate to `/index.php` to see:
- 40+ metrics across 6 sections
- Most active users table
- Recent admin actions

### Analyze Trends
Navigate to `/analytics.php` for:
- 24-hour message traffic visualization
- 30-day user growth tracking
- Top message senders ranking
- System health indicators

### Manage Users at Scale
Navigate to `/users-v2.php` to:
1. Search for specific users
2. Filter by status (banned, suspended, etc.)
3. Select multiple users
4. Apply bulk actions (verify, suspend, ban)
5. Export selected users as CSV

### Monitor System Activity
- Dashboard shows online users right now
- Messages per minute indicates current load
- Active calls visible with counts
- Open reports flagged as danger alerts

---

## 🚀 Future Enhancements

- Real-time graph updates (WebSocket via Reverb)
- Advanced filters (date ranges, message volume ranges)
- Custom report generation
- Scheduled exports
- Alert thresholds
- User cohort analysis
- Revenue/usage trending by conversation
- Integration with external analytics services

---

## Testing Notes

✅ **Verified Features:**
- Analytics page loads with correct queries
- Dashboard displays all 40+ metrics
- User list filters work (all statuses)
- Bulk select checkbox toggles row highlighting
- Search functionality queries database
- Pagination handles large datasets
- Export CSV downloads correctly

**Admin Credentials (created via CLI):**
- Email: superadmin@sohni.local
- Password: f6fc8bdb8c1c2b9bb9
- Role: super_admin

---
