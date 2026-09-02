# 🧪 Security Implementation Testing Checklist

## Pre-Testing Setup

- [ ] Run database migration: `c:\xampp\php\php.exe artisan migrate`
- [ ] Clear cache: `c:\xampp\php\php.exe artisan config:clear`
- [ ] Start XAMPP Apache & MySQL
- [ ] Access application at http://127.0.0.1:8000

---

## Session Timeout Testing

### Test 1: Verify Session Timeout Middleware Active
1. [ ] Create new user account via signup
2. [ ] Login successfully
3. [ ] Modify database: Set user's `session_timeout_hours` = 1 (for quick testing)
4. [ ] Navigate to any protected page (dashboard)
5. [ ] Wait 1+ hour (or manually update `last_activity_at` to past timestamp)
6. [ ] Try to access dashboard
7. **Expected**: Redirected to login with "session_expired" message

### Test 2: Verify Last Activity Update
1. [ ] Create test user
2. [ ] Login and navigate to dashboard
3. [ ] Check database: `last_activity_at` should be current timestamp
4. [ ] Wait 30 seconds
5. [ ] Refresh page or navigate to different page
6. [ ] Check database: `last_activity_at` should update to new timestamp
7. **Expected**: Timestamp updates on every request

### Test 3: Session Persists Within Timeout
1. [ ] Create test user with 24-hour timeout
2. [ ] Login successfully
3. [ ] Verify redirect to dashboard (not verify-security)
4. [ ] Refresh page multiple times
5. [ ] Navigate between dashboard → profile → settings
6. **Expected**: No redirect to verify-security, session active

---

## PIN Verification Testing

### Test 1: Set Security PIN
1. [ ] Goto Settings page
2. [ ] Click "Set PIN" under Security Settings
3. [ ] Enter PIN: `1234`
4. [ ] Submit
5. [ ] Check database: `security_pin` column contains bcrypt hash (starts with $2y$)
6. **Expected**: Success toast, "Remove" button appears

### Test 2: PIN Verification Redirect
1. [ ] Enable "Require PIN on Login" toggle (if available in UI, or test manually)
2. [ ] Logout
3. [ ] Login again with same credentials
4. [ ] **Expected**: Redirected to `/verify-security` page with PIN tab active
5. [ ] NOT redirected to dashboard

### Test 3: PIN Verification Success
1. [ ] On verify-security page, enter PIN: `1234`
2. [ ] Verify each digit fills in the display circles
3. [ ] Click "Verify PIN"
4. [ ] **Expected**: Success message, redirected to dashboard
5. [ ] Check database: `security_verified_at` session variable set

### Test 4: PIN Verification Failure
1. [ ] On verify-security page, enter PIN: `9999` (incorrect)
2. [ ] Click "Verify PIN"
3. [ ] **Expected**: Error toast "Invalid PIN", fields cleared
4. [ ] Try again with correct PIN: `1234`
5. [ ] **Expected**: Success

### Test 5: PIN Re-verification Every 30 Minutes
1. [ ] Set user's PIN and enable requirement
2. [ ] Login → verify PIN → dashboard (success)
3. [ ] Navigate around dashboard/profile (should work)
4. [ ] Manually set `session('security_verified_at')` to 31+ minutes ago
5. [ ] Try to access any protected page (dashboard, profile, settings)
6. [ ] **Expected**: Redirected to `/verify-security` for re-verification

### Test 6: Remove PIN
1. [ ] On Settings page, click "Remove" under PIN section
2. [ ] Enter your password
3. [ ] Submit
4. [ ] **Expected**: Success toast, "Remove" button disappears
5. [ ] Check database: `security_pin` = NULL
6. [ ] Logout and login again
7. [ ] **Expected**: No redirect to verify-security (goes straight to dashboard)

---

## Pattern Lock Testing

### Test 1: Set Security Pattern
1. [ ] On Settings page, click "Set Pattern" under Security Settings
2. [ ] Enter pattern: `123456789` (all dots in order)
3. [ ] Submit
4. [ ] **Expected**: Success toast, "Remove" button appears
5. [ ] Check database: `security_pattern` contains bcrypt hash

### Test 2: Pattern Verification Page (Visual)
1. [ ] Enable pattern requirement
2. [ ] Logout and login
3. [ ] Navigate to `/verify-security`
4. [ ] **Expected**: Pattern tab should show:
   - 3×3 grid of numbered dots (1-9)
   - 3 empty circles at top (pattern display)
   - Each dot clickable with hover effect
5. [ ] Click dots 1, 2, 3
6. [ ] **Expected**: 
   - Dots light up with neon gradient
   - Display circles fill in as you select
   - Numbers appear in order (1, 2, 3)

### Test 3: Pattern Verification Success
1. [ ] Click dots: `1, 2, 3, 4` (any valid 4+ sequence)
2. [ ] Note the sequence you used
3. [ ] Click "Verify Pattern"
4. [ ] **Expected**: Error "Invalid pattern"
5. [ ] Click "Reset"
6. [ ] **Expected**: Dots unlight, display circles empty
7. [ ] Draw same pattern again
8. [ ] Click "Verify Pattern"
9. [ ] **Expected**: Success, redirected to dashboard

### Test 4: Pattern Verification Failure
1. [ ] Draw pattern: `1, 2, 3, 4`
2. [ ] Click "Verify Pattern"
3. [ ] **Expected**: "Invalid pattern" error if pattern doesn't match stored one
4. [ ] Fields reset automatically

### Test 5: Remove Pattern
1. [ ] On Settings page, click "Remove" under Pattern section
2. [ ] Enter password
3. [ ] Submit
4. [ ] **Expected**: Success toast, "Remove" button disappears
5. [ ] Check database: `security_pattern` = NULL

---

## Dual Verification Testing (PIN + Pattern)

### Test 1: Both PIN and Pattern Enabled
1. [ ] Set PIN: `1234`
2. [ ] Set Pattern: `1-2-3-4` (sequence)
3. [ ] Enable both requirement toggles
4. [ ] Logout and login
5. [ ] Navigate to `/verify-security`
6. [ ] **Expected**: Both PIN and Pattern tabs visible
7. [ ] Try PIN verification
8. [ ] **Expected**: Tab switches work, both methods work independently

### Test 2: Switch Between Methods
1. [ ] On verify-security, PIN tab is active by default
2. [ ] Click Pattern tab
3. [ ] **Expected**: Pattern grid loads, pattern circles visible
4. [ ] Click PIN tab
5. [ ] **Expected**: Back to PIN entry fields
6. [ ] Verify with whichever method (both should work)

---

## Advanced Privacy & Session Controls Testing

### Test 1: Update Session Timeout
1. [ ] On Settings page, check if session timeout selector exists
2. [ ] **Note**: If not yet in UI, test via API:
   ```bash
   curl -X POST http://127.0.0.1:8000/api/settings/session/timeout \
     -H "X-CSRF-TOKEN: {token}" \
     -d "hours=6"
   ```
3. [ ] Check database: `session_timeout_hours` = 6
4. [ ] Session should now expire after 6 hours instead of 72

### Test 2: Get Login History
1. [ ] Test via API:
   ```bash
   curl http://127.0.0.1:8000/api/settings/login-history
   ```
2. [ ] **Expected**: JSON array of login records
3. [ ] **Note**: Will be empty until AuthController integration (future)

### Test 3: Block User
1. [ ] Test via API (use another user's ID):
   ```bash
   curl -X POST http://127.0.0.1:8000/api/settings/blocked-users/add \
     -H "X-CSRF-TOKEN: {token}" \
     -d "user_id=2"
   ```
2. [ ] Check database: `blocked_users` JSON contains user ID 2
3. [ ] **Expected**: Success message

### Test 4: Get Blocked Users
1. [ ] Test via API:
   ```bash
   curl http://127.0.0.1:8000/api/settings/blocked-users
   ```
2. [ ] **Expected**: JSON array with blocked user info (id, name, email, avatar)

### Test 5: End All Sessions
1. [ ] Test via API:
   ```bash
   curl -X POST http://127.0.0.1:8000/api/settings/session/end-all \
     -H "X-CSRF-TOKEN: {token}"
   ```
2. [ ] Check database: `active_devices` = empty array
3. [ ] **Expected**: All other sessions cleared

---

## Cross-Browser / Multi-Tab Testing

### Test 1: Verification Per-Tab
1. [ ] Open dashboard in Tab A
2. [ ] Open same dashboard in Tab B
3. [ ] Manually set `security_verified_at` to 31+ minutes ago in both tabs
4. [ ] Refresh Tab A
5. [ ] **Expected**: Redirect to verify-security
6. [ ] Refresh Tab B
7. [ ] **Expected**: Also redirect to verify-security
8. [ ] Verify PIN in Tab A
9. [ ] **Expected**: Session marked in Tab A
10. [ ] Return to Tab B
11. [ ] **Expected**: Tab B still redirects to verify-security (separate session)

---

## UI/UX Testing

### Settings Page
- [ ] Sidebar has shadow and border visible
- [ ] All 5 tabs work (Privacy, Security, Devices, Password, Account)
- [ ] PIN modals open/close correctly
- [ ] Pattern modals open/close correctly
- [ ] Remove buttons only show when PIN/Pattern set
- [ ] Toast notifications appear on success/error
- [ ] Form validation works (PIN must be 4 digits, pattern 4+ chars)

### Verify-Security Page
- [ ] Logo and title display correctly
- [ ] PIN and Pattern tabs both visible
- [ ] PIN tab: 4 input fields with proper spacing
- [ ] Pattern tab: 3×3 dot grid with numbers 1-9
- [ ] Pattern display: 3 circles at top
- [ ] Spinner shows during verification
- [ ] Success/error alerts display
- [ ] Back to dashboard link works
- [ ] Responsive on mobile (single column, smaller dots)

---

## Database Verification

### Columns Created
```sql
SELECT 
  require_pin_on_login, require_pattern_on_login,
  last_activity_at, last_login_at,
  login_history, blocked_users,
  allow_message_requests, allow_group_invites,
  allow_video_calls, allow_screen_share,
  session_timeout_hours, trusted_devices,
  two_factor_enabled
FROM users
LIMIT 1;
```

- [ ] All 13 new columns exist
- [ ] Proper data types (boolean, json, datetime)
- [ ] Default values set correctly (boolean defaults to false)

---

## Security Validation Tests

### Test 1: Cannot Bypass PIN/Pattern
1. [ ] Enable PIN requirement
2. [ ] Logout
3. [ ] Login with correct credentials
4. [ ] Try to directly access `/dashboard` without verify-security
5. [ ] **Expected**: Middleware redirects to `/verify-security`
6. [ ] Cannot access any protected page without verification

### Test 2: Session Cannot Be Stolen
1. [ ] Create session and get session ID
2. [ ] In new browser/incognito, try to use same session ID
3. [ ] Try to access protected page
4. [ ] **Expected**: Redirected to login (different IP/user agent detected)

### Test 3: Timeout Cannot Be Bypassed
1. [ ] Set timeout to 1 hour
2. [ ] Login and access dashboard
3. [ ] Manually modify database: `last_activity_at` = 2 hours ago
4. [ ] Try to access any protected page
5. [ ] **Expected**: Session destroyed, redirected to login with timeout message

---

## Error Handling Tests

### Invalid Input
- [ ] PIN: Enter letters (should reject or filter)
- [ ] PIN: Enter less than 4 digits (should warn)
- [ ] PIN: Enter more than 4 digits (should truncate or warn)
- [ ] Pattern: Less than 4 dots (should warn)
- [ ] Pattern: Duplicate dots in sequence (depends on implementation)

### Edge Cases
- [ ] User deletes their own PIN while session active
- [ ] User changes pattern while verification window active
- [ ] User logs out during verification
- [ ] User's session_timeout_hours deleted/null (should use default)
- [ ] Multiple concurrent logins with same account

---

## Performance Tests

- [ ] Middleware doesn't significantly slow down requests
- [ ] Verify-security page loads quickly (< 2 seconds)
- [ ] Pattern drawing is smooth (no lag on dot selection)
- [ ] Session timeout check doesn't query database on every request
- [ ] Large login_history JSON doesn't cause issues

---

## Final Deployment Checklist

- [ ] All syntax checks pass (PHP -l on all files)
- [ ] Database migration applied
- [ ] Config cache cleared
- [ ] Middleware registered in bootstrap/app.php
- [ ] No console errors (check browser dev tools)
- [ ] No PHP errors in server logs
- [ ] All tests above pass
- [ ] Documentation updated
- [ ] User guide created (optional)
- [ ] Security audit completed
- [ ] Load testing on session timeout feature

---

## Reporting Issues

If any test fails, note:
1. Which test failed
2. Expected vs actual behavior
3. Error messages (console, PHP error log, response JSON)
4. Steps to reproduce
5. Database state at time of failure
6. Browser/device information

---

**Total Tests**: 30+
**Estimated Time**: 2-3 hours
**Critical Path Tests**: 1, 5, 6, 9, 13 (minimum 30 minutes)

**Status**: Ready for testing ✅
