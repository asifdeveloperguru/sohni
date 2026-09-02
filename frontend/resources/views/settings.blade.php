<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Settings — Sohni</title>
    <link rel="icon" type="image/png" href="/images/app_logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --neon: #0084ff;
            --neon-2: #00d4ff;
            --neon-soft: #e8f4ff;
            --ink: #0f172a;
            --text: #1f2937;
            --muted: #6b7280;
            --line: #e8eef5;
            --surface: #ffffff;
            --tint: #f7fbff;
            --success: #16a34a;
            --danger: #ef4444;
            --warning: #f59e0b;
            --shadow-sm: 0 1px 2px rgba(15, 23, 42, .04), 0 2px 8px rgba(15, 23, 42, .04);
            --shadow-md: 0 4px 12px rgba(15, 23, 42, .05), 0 12px 32px rgba(15, 23, 42, .06);
            --shadow-neon: 0 8px 24px rgba(0, 132, 255, .18);
            --r-sm: 10px;
            --r-md: 14px;
            --r-lg: 20px;
        }

        html, body { height: 100%; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--surface);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, .display { font-family: 'Space Grotesk', sans-serif; }

        .loader {
            position: fixed;
            inset: 0;
            background: var(--surface);
            z-index: 2000;
            display: grid;
            place-content: center;
            justify-items: center;
            gap: 18px;
            transition: opacity .4s ease, visibility .4s ease;
        }
        .loader.hidden { opacity: 0; visibility: hidden; }
        .loader .ring {
            width: 46px; height: 46px;
            border: 3px solid var(--neon-soft);
            border-top-color: var(--neon);
            border-radius: 50%;
            animation: spin .85s linear infinite;
        }
        .loader p {
            font-family: 'Sora', sans-serif;
            font-size: 11px; font-weight: 600;
            letter-spacing: 2px; text-transform: uppercase;
            color: var(--muted);
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .page { opacity: 0; transition: opacity .45s ease; }
        .page.ready { opacity: 1; }

        .topbar {
            position: sticky; top: 0; z-index: 100;
            background: rgba(255, 255, 255, .82);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--line);
        }
        .topbar-inner {
            max-width: 100%;
            width: 100%;
            margin: 0 auto;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .brand { display: flex; align-items: center; gap: 11px; text-decoration: none; }
        .brand img { height: 30px; width: auto; }
        .brand span {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 19px; font-weight: 700;
            background: linear-gradient(120deg, var(--neon), var(--neon-2));
            -webkit-background-clip: text; background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .topbar-actions { display: flex; gap: 10px; }

        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 7px;
            font-family: 'Sora', sans-serif;
            font-size: 13px; font-weight: 600;
            padding: 9px 18px; border-radius: 999px;
            border: 1px solid var(--line);
            background: var(--surface); color: var(--text);
            cursor: pointer; white-space: nowrap;
            transition: all .22s cubic-bezier(.34, 1.4, .64, 1);
            text-decoration: none;
        }
        .btn:hover {
            border-color: var(--neon); color: var(--neon);
            background: var(--tint);
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(0, 132, 255, .14);
        }
        .btn.neon {
            background: linear-gradient(120deg, var(--neon), var(--neon-2));
            color: #fff; border-color: transparent;
            box-shadow: var(--shadow-neon);
        }
        .btn.neon:hover {
            color: #fff; transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(0, 132, 255, .32);
        }
        .btn.ghost-danger { border-color: #fadcdc; color: var(--danger); }
        .btn.ghost-danger:hover { background: #fff5f5; border-color: var(--danger); color: var(--danger); box-shadow: 0 4px 14px rgba(239, 68, 68, .14); }

        .shell {
            max-width: 100%;
            width: 100%;
            margin: 0 auto;
            padding: 26px 24px 60px;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
            gap: 24px;
            align-items: start;
            margin: 0 auto;
            width: 100%;
        }

        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 8px;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--r-lg);
            padding: 12px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, .05), inset 0 0 0 1px rgba(255, 255, 255, .8);
        }

        .sidebar-item {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 16px; border-radius: var(--r-md);
            border: 1px solid transparent; background: transparent;
            cursor: pointer; white-space: nowrap;
            font-size: 14px; font-weight: 600;
            color: var(--muted);
            transition: all .2s ease;
            box-shadow: inset 0 0 0 1px transparent;
        }
        .sidebar-item i { font-size: 15px; }
        .sidebar-item:hover {
            background: var(--tint);
            border-color: #d8e9fb;
            color: var(--neon);
            box-shadow: inset 0 0 0 1px #d8e9fb;
            transform: translateX(2px);
        }
        .sidebar-item.active {
            background: linear-gradient(120deg, var(--neon), var(--neon-2));
            border-color: transparent;
            color: #fff;
            box-shadow: var(--shadow-neon), inset 0 0 0 1px rgba(255, 255, 255, .3);
            transform: translateX(0);
        }

        .content {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .panel {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--r-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        .panel-head {
            display: flex; align-items: center; gap: 12px;
            padding: 20px 24px; 
            border-bottom: 1px solid var(--line);
            background: linear-gradient(180deg, var(--tint), #fff);
        }
        .panel-head .ico {
            width: 40px; height: 40px; border-radius: 12px;
            display: grid; place-content: center;
            background: linear-gradient(135deg, var(--neon), var(--neon-2));
            color: #fff; font-size: 15px;
            box-shadow: var(--shadow-neon);
        }
        .panel-head h2 { font-size: 17px; font-weight: 700; color: var(--ink); }
        .panel-head .sub { font-size: 12px; color: var(--muted); margin-top: 2px; font-weight: 500; }

        .panel-body { padding: 24px; }

        .setting-row {
            display: flex; align-items: center; justify-content: space-between; gap: 16px;
            padding: 16px 0; border-bottom: 1px solid var(--line);
        }
        .setting-row:last-child { border-bottom: none; }
        .setting-label {
            flex: 1; min-width: 0;
        }
        .setting-label h3 { font-size: 14px; font-weight: 700; color: var(--ink); margin-bottom: 4px; }
        .setting-label p { font-size: 12px; color: var(--muted); }

        .toggle {
            position: relative;
            width: 52px; height: 28px;
            background: var(--line);
            border-radius: 999px;
            cursor: pointer;
            transition: all .3s ease;
        }
        .toggle.active { background: var(--success); }
        .toggle::after {
            content: '';
            position: absolute;
            width: 24px; height: 24px;
            background: #fff;
            border-radius: 50%;
            top: 2px; left: 2px;
            transition: left .3s ease;
        }
        .toggle.active::after { left: 26px; }

        .form-group {
            margin-bottom: 18px;
        }
        .form-group:last-child { margin-bottom: 0; }
        .form-group label {
            display: block; font-size: 13px; font-weight: 600;
            color: var(--ink); margin-bottom: 8px;
        }
        .form-group input[type="text"],
        .form-group input[type="password"],
        .form-group input[type="email"],
        .form-group textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--line);
            border-radius: var(--r-md);
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            color: var(--text);
            background: var(--surface);
            transition: all .2s ease;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--neon);
            box-shadow: 0 0 0 3px rgba(0, 132, 255, .1);
        }

        .row-group {
            display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;
        }

        .device-item {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 14px;
            border: 1px solid var(--line);
            border-radius: var(--r-md);
            background: var(--tint);
            margin-bottom: 12px;
        }
        .device-info { flex: 1; }
        .device-name { font-size: 14px; font-weight: 600; color: var(--ink); }
        .device-meta { font-size: 11px; color: var(--muted); margin-top: 4px; }
        .device-actions { display: flex; gap: 8px; }

        .alert {
            padding: 16px;
            border-radius: var(--r-md);
            margin-bottom: 16px;
            border-left: 4px solid;
            display: flex; align-items: flex-start; gap: 12px;
        }
        .alert i { font-size: 16px; flex-shrink: 0; }
        .alert.warning {
            background: #fffbeb;
            border-left-color: var(--warning);
            color: #78350f;
        }
        .alert.danger {
            background: #fef2f2;
            border-left-color: var(--danger);
            color: #7f1d1d;
        }

        .btn-group {
            display: flex; gap: 10px;
            margin-top: 16px;
        }
        .btn-group .btn { flex: 1; }

        .modal {
            position: fixed; inset: 0; z-index: 1000;
            display: none; place-content: center;
            background: rgba(15, 23, 42, .55);
            backdrop-filter: blur(4px);
            padding: 20px;
        }
        .modal.active { display: grid; }
        .modal-box {
            width: min(480px, 100%);
            max-height: 88vh; overflow-y: auto;
            background: var(--surface);
            border-radius: var(--r-lg);
            padding: 26px;
            box-shadow: 0 30px 70px rgba(15, 23, 42, .3);
            animation: rise .3s ease;
        }
        .modal-head {
            display: flex; align-items: center; justify-content: space-between;
            gap: 16px; margin-bottom: 20px;
        }
        .modal-head h3 { font-size: 18px; font-weight: 700; color: var(--ink); }
        .modal-close {
            width: 34px; height: 34px; border-radius: 50%;
            border: 1px solid var(--line); background: var(--tint);
            color: var(--muted); font-size: 15px; cursor: pointer;
            display: grid; place-content: center;
            transition: all .2s ease;
        }
        .modal-close:hover { background: #fff5f5; border-color: var(--danger); color: var(--danger); }

        @keyframes rise { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }

        .toast {
            position: fixed; left: 50%; bottom: 28px;
            transform: translate(-50%, 20px);
            display: flex; align-items: center; gap: 9px;
            padding: 12px 20px; border-radius: 999px;
            background: var(--ink); color: #fff;
            font-size: 13px; font-weight: 600;
            box-shadow: 0 14px 34px rgba(15, 23, 42, .3);
            opacity: 0; visibility: hidden; z-index: 3000;
            transition: all .3s cubic-bezier(.34, 1.4, .64, 1);
        }
        .toast.show { opacity: 1; visibility: visible; transform: translate(-50%, 0); }
        .toast.success { background: var(--success); }
        .toast.error { background: var(--danger); }
        .toast i { color: #fff; }

        @media (max-width: 768px) {
            .settings-grid { grid-template-columns: 1fr; }
            .sidebar { flex-direction: row; flex-wrap: wrap; }
            .sidebar-item { flex: 1; min-width: 140px; padding: 10px 12px; font-size: 12px; }
            .row-group { grid-template-columns: 1fr; }
            .shell { padding: 20px 16px 50px; }
        }

        .pane { display: none; }
        .pane.active { display: block; }
    </style>
</head>
<body>

<div class="loader" id="loader">
    <div class="ring"></div>
    <p>Loading settings</p>
</div>

<div class="page" id="page">

    <header class="topbar">
        <div class="topbar-inner">
            <a href="/dashboard" class="brand">
                <img src="/images/app_logo.png" alt="Sohni">
                <span>Sohni</span>
            </a>
            <div class="topbar-actions">
                <a href="/profile" class="btn"><i class="fas fa-user"></i> Profile</a>
                <button class="btn ghost-danger" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </div>
        </div>
    </header>

    <main class="shell">
        <div class="settings-grid">

            <!-- Sidebar Navigation -->
            <aside class="sidebar">
                <button class="sidebar-item active" onclick="switchPane('privacy')" data-pane="privacy">
                    <i class="fas fa-shield-alt"></i> Privacy
                </button>
                <button class="sidebar-item" onclick="switchPane('security')" data-pane="security">
                    <i class="fas fa-lock"></i> Security
                </button>
                <button class="sidebar-item" onclick="switchPane('devices')" data-pane="devices">
                    <i class="fas fa-mobile-alt"></i> Devices
                </button>
                <button class="sidebar-item" onclick="switchPane('password')" data-pane="password">
                    <i class="fas fa-key"></i> Password
                </button>
                <button class="sidebar-item" onclick="switchPane('account')" data-pane="account">
                    <i class="fas fa-exclamation-triangle"></i> Account
                </button>
            </aside>

            <!-- Content Area -->
            <div class="content">

                <!-- PRIVACY SETTINGS -->
                <div class="pane active" id="pane-privacy">
                    <div class="panel">
                        <div class="panel-head">
                            <div class="ico"><i class="fas fa-shield-alt"></i></div>
                            <div>
                                <h2>Privacy Settings</h2>
                                <div class="sub">Control who can interact with you</div>
                            </div>
                        </div>
                        <div class="panel-body">

                            <div class="setting-row">
                                <div class="setting-label">
                                    <h3><i class="fas fa-user-plus"></i> Accept Friend Requests</h3>
                                    <p>Allow others to send you friend requests</p>
                                </div>
                                <div class="toggle active" id="toggle-friend-requests" onclick="toggleSetting(event, 'accept_friend_requests')"></div>
                            </div>

                            <div class="setting-row">
                                <div class="setting-label">
                                    <h3><i class="fas fa-circle"></i> Show Online Status</h3>
                                    <p>Let others see when you're online</p>
                                </div>
                                <div class="toggle active" id="toggle-online-status" onclick="toggleSetting(event, 'show_online_status')"></div>
                            </div>

                            <div class="setting-row">
                                <div class="setting-label">
                                    <h3><i class="fas fa-keyboard"></i> Show Typing Indicators</h3>
                                    <p>Show when you're typing a message</p>
                                </div>
                                <div class="toggle active" id="toggle-typing-indicators" onclick="toggleSetting(event, 'show_typing_indicators')"></div>
                            </div>

                            <div class="setting-row">
                                <div class="setting-label">
                                    <h3><i class="fas fa-globe"></i> Profile Visibility</h3>
                                    <p>Make your profile public for followers</p>
                                </div>
                                <div class="toggle active" id="toggle-profile-public" onclick="toggleSetting(event, 'profile_public')"></div>
                            </div>

                            <div class="setting-row">
                                <div class="setting-label">
                                    <h3><i class="fas fa-qrcode"></i> Accept QR Code Requests</h3>
                                    <p>Allow chat requests via QR code scan</p>
                                </div>
                                <div class="toggle active" id="toggle-qr-requests" onclick="toggleSetting(event, 'accept_qr_requests')"></div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- SECURITY SETTINGS -->
                <div class="pane" id="pane-security">
                    <div class="panel">
                        <div class="panel-head">
                            <div class="ico"><i class="fas fa-lock"></i></div>
                            <div>
                                <h2>Security Settings</h2>
                                <div class="sub">Protect your account with additional security</div>
                            </div>
                        </div>
                        <div class="panel-body">

                            <div class="alert warning">
                                <i class="fas fa-info-circle"></i>
                                <div>Enhanced security features add an extra layer of protection to your account when accessing from new devices.</div>
                            </div>

                            <!-- Security PIN -->
                            <div style="margin-bottom: 30px;">
                                <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 12px; color: var(--ink);">
                                    <i class="fas fa-pin"></i> Security PIN
                                </h3>
                                <p style="font-size: 12px; color: var(--muted); margin-bottom: 14px;">A 4-digit PIN for additional account access protection</p>
                                <div class="row-group">
                                    <button class="btn neon" onclick="openSetPinModal()"><i class="fas fa-plus"></i> Set PIN</button>
                                    <button class="btn ghost-danger" id="remove-pin-btn" style="display: none;" onclick="openRemovePinModal()"><i class="fas fa-trash"></i> Remove</button>
                                </div>
                            </div>

                            <!-- Security Pattern -->
                            <div style="border-top: 1px solid var(--line); padding-top: 20px;">
                                <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 12px; color: var(--ink);">
                                    <i class="fas fa-grip"></i> Security Pattern
                                </h3>
                                <p style="font-size: 12px; color: var(--muted); margin-bottom: 14px;">Create a custom pattern for device access</p>
                                <div class="row-group">
                                    <button class="btn neon" onclick="openSetPatternModal()"><i class="fas fa-plus"></i> Set Pattern</button>
                                    <button class="btn ghost-danger" id="remove-pattern-btn" style="display: none;" onclick="openRemovePatternModal()"><i class="fas fa-trash"></i> Remove</button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- DEVICE MANAGEMENT -->
                <div class="pane" id="pane-devices">
                    <div class="panel">
                        <div class="panel-head">
                            <div class="ico"><i class="fas fa-mobile-alt"></i></div>
                            <div>
                                <h2>Manage Devices</h2>
                                <div class="sub">View and manage devices using your account</div>
                            </div>
                        </div>
                        <div class="panel-body">

                            <div style="margin-bottom: 20px;">
                                <button class="btn neon" onclick="registerDevice()"><i class="fas fa-plus"></i> Register This Device</button>
                            </div>

                            <div id="devicesList">
                                <p style="text-align: center; color: var(--muted); padding: 20px;">No devices registered yet</p>
                            </div>

                            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--line);">
                                <button class="btn ghost-danger" onclick="logoutOtherDevices()"><i class="fas fa-sign-out-alt"></i> Logout from All Other Devices</button>
                                <p style="font-size: 11px; color: var(--muted); margin-top: 8px;">This will sign you out from all devices except this one</p>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- PASSWORD CHANGE -->
                <div class="pane" id="pane-password">
                    <div class="panel">
                        <div class="panel-head">
                            <div class="ico"><i class="fas fa-key"></i></div>
                            <div>
                                <h2>Change Password</h2>
                                <div class="sub">Update your login password</div>
                            </div>
                        </div>
                        <div class="panel-body">

                            <form onsubmit="changePassword(event)">
                                <div class="form-group">
                                    <label>Current Password</label>
                                    <input type="password" id="current_password" required>
                                </div>

                                <div class="form-group">
                                    <label>New Password</label>
                                    <input type="password" id="new_password" required>
                                    <small style="color: var(--muted); display: block; margin-top: 6px;">
                                        At least 8 characters, with uppercase, lowercase, and numbers
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label>Confirm New Password</label>
                                    <input type="password" id="password_confirmation" required>
                                </div>

                                <button type="submit" class="btn neon" style="width: 100%;"><i class="fas fa-check"></i> Update Password</button>
                            </form>

                        </div>
                    </div>
                </div>

                <!-- ACCOUNT SETTINGS -->
                <div class="pane" id="pane-account">
                    <div class="panel">
                        <div class="panel-head">
                            <div class="ico"><i class="fas fa-exclamation-triangle"></i></div>
                            <div>
                                <h2>Account Management</h2>
                                <div class="sub">Dangerous zone - handle with care</div>
                            </div>
                        </div>
                        <div class="panel-body">

                            <div class="alert danger">
                                <i class="fas fa-warning"></i>
                                <div>
                                    <strong>Warning:</strong> Actions in this section cannot be undone. Proceed with caution.
                                </div>
                            </div>

                            <div style="margin-bottom: 30px; padding: 16px; background: #fef2f2; border-radius: var(--r-md); border-left: 4px solid var(--danger);">
                                <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 8px; color: var(--danger);">
                                    <i class="fas fa-trash-alt"></i> Delete Account
                                </h3>
                                <p style="font-size: 12px; color: #7f1d1d; margin-bottom: 12px;">
                                    Permanently delete your account and all associated data. This action cannot be undone.
                                </p>
                                <button class="btn ghost-danger" onclick="openDeleteAccountModal()">
                                    <i class="fas fa-trash-alt"></i> Delete My Account
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

        </div>
    </main>

</div>

<!-- SET PIN MODAL -->
<div class="modal" id="setPinModal">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="fas fa-pin"></i> Set Security PIN</h3>
            <button class="modal-close" onclick="closePinModal()"><i class="fas fa-xmark"></i></button>
        </div>
        <form onsubmit="setPin(event)">
            <div class="form-group">
                <label>Enter 4-Digit PIN</label>
                <input type="text" id="pin_input" maxlength="4" pattern="\d{4}" placeholder="0000" required>
                <small style="color: var(--muted); display: block; margin-top: 6px;">Enter exactly 4 digits (0-9)</small>
            </div>
            <button type="submit" class="btn neon" style="width: 100%;">Set PIN</button>
        </form>
    </div>
</div>

<!-- REMOVE PIN MODAL -->
<div class="modal" id="removePinModal">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="fas fa-trash"></i> Remove PIN</h3>
            <button class="modal-close" onclick="closePinModal()"><i class="fas fa-xmark"></i></button>
        </div>
        <form onsubmit="removePin(event)">
            <div class="form-group">
                <label>Confirm Your Password</label>
                <input type="password" id="pin_remove_password" required>
            </div>
            <button type="submit" class="btn ghost-danger" style="width: 100%;">Remove PIN</button>
        </form>
    </div>
</div>

<!-- SET PATTERN MODAL -->
<div class="modal" id="setPatternModal">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="fas fa-grip"></i> Set Security Pattern</h3>
            <button class="modal-close" onclick="closePatternModal()"><i class="fas fa-xmark"></i></button>
        </div>
        <form onsubmit="setPattern(event)">
            <div class="form-group">
                <label>Create Your Pattern</label>
                <input type="text" id="pattern_input" placeholder="e.g., 123456-789" minlength="4" required>
                <small style="color: var(--muted); display: block; margin-top: 6px;">Minimum 4 characters</small>
            </div>
            <button type="submit" class="btn neon" style="width: 100%;">Set Pattern</button>
        </form>
    </div>
</div>

<!-- REMOVE PATTERN MODAL -->
<div class="modal" id="removePatternModal">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="fas fa-trash"></i> Remove Pattern</h3>
            <button class="modal-close" onclick="closePatternModal()"><i class="fas fa-xmark"></i></button>
        </div>
        <form onsubmit="removePattern(event)">
            <div class="form-group">
                <label>Confirm Your Password</label>
                <input type="password" id="pattern_remove_password" required>
            </div>
            <button type="submit" class="btn ghost-danger" style="width: 100%;">Remove Pattern</button>
        </form>
    </div>
</div>

<!-- DELETE ACCOUNT MODAL -->
<div class="modal" id="deleteAccountModal">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="fas fa-exclamation-circle"></i> Delete Account</h3>
            <button class="modal-close" onclick="closeDeleteAccountModal()"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="alert danger" style="margin-bottom: 16px;">
            <i class="fas fa-warning"></i>
            <div>
                <strong>This action is permanent!</strong> Your account and all data will be deleted immediately.
            </div>
        </div>
        <form onsubmit="deleteAccount(event)">
            <div class="form-group">
                <label>Enter Your Password</label>
                <input type="password" id="delete_password" required>
            </div>
            <div class="form-group">
                <label>Type "DELETE" to confirm</label>
                <input type="text" id="delete_confirmation" placeholder="DELETE" required>
            </div>
            <button type="submit" class="btn ghost-danger" style="width: 100%;">Permanently Delete Account</button>
        </form>
    </div>
</div>

<div class="toast" id="toast">
    <i class="fas fa-circle-check"></i>
    <span id="toastText"></span>
</div>

<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    let settings = null;

    document.addEventListener('DOMContentLoaded', loadSettings);
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', e => {
            if (e.target === modal) modal.classList.remove('active');
        });
    });

    function esc(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    function toast(msg, type = 'success') {
        const t = document.getElementById('toast');
        document.getElementById('toastText').textContent = msg;
        t.className = 'toast ' + type;
        t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), 2600);
    }

    async function loadSettings() {
        try {
            const res = await fetch('/api/settings', { headers: { 'Accept': 'application/json' } });
            if (res.status === 401) { window.location.href = '/account'; return; }
            const json = await res.json();
            if (!json.success) return;

            settings = json.data;
            renderSettings();
            await loadDevices();
        } catch (e) {
            console.error('Settings load error:', e);
        } finally {
            document.getElementById('loader').classList.add('hidden');
            document.getElementById('page').classList.add('ready');
        }
    }

    function renderSettings() {
        const privacy = settings.privacy;
        
        // Update toggles
        updateToggle('toggle-friend-requests', privacy.accept_friend_requests);
        updateToggle('toggle-online-status', privacy.show_online_status);
        updateToggle('toggle-typing-indicators', privacy.show_typing_indicators);
        updateToggle('toggle-profile-public', privacy.profile_public);
        updateToggle('toggle-qr-requests', privacy.accept_qr_requests);

        // Update security UI
        const security = settings.security;
        document.getElementById('remove-pin-btn').style.display = security.pin_enabled ? 'block' : 'none';
        document.getElementById('remove-pattern-btn').style.display = security.pattern_enabled ? 'block' : 'none';
    }

    function updateToggle(id, state) {
        const toggle = document.getElementById(id);
        if (state) {
            toggle.classList.add('active');
        } else {
            toggle.classList.remove('active');
        }
    }

    function switchPane(pane) {
        document.querySelectorAll('.pane').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('active'));
        document.getElementById('pane-' + pane).classList.add('active');
        document.querySelector(`[data-pane="${pane}"]`).classList.add('active');
    }

    async function toggleSetting(event, settingName) {
        const toggle = event.currentTarget;
        const isActive = toggle.classList.contains('active');
        const newValue = !isActive;

        toggle.classList.toggle('active');

        try {
            const res = await fetch('/api/settings/privacy', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({ [settingName]: newValue })
            });
            const json = await res.json();
            if (!json.success) {
                toast(json.message || 'Failed to update setting', 'error');
                toggle.classList.toggle('active');
            } else {
                toast('Setting updated');
            }
        } catch (e) {
            console.error(e);
            toast('Error updating setting', 'error');
            toggle.classList.toggle('active');
        }
    }

    // PIN Functions
    function openSetPinModal() {
        document.getElementById('setPinModal').classList.add('active');
        document.getElementById('pin_input').value = '';
        document.getElementById('pin_input').focus();
    }

    function closePinModal() {
        document.getElementById('setPinModal').classList.remove('active');
        document.getElementById('removePinModal').classList.remove('active');
    }

    async function setPin(event) {
        event.preventDefault();
        const pin = document.getElementById('pin_input').value;

        if (pin.length !== 4 || !/^\d+$/.test(pin)) {
            toast('PIN must be exactly 4 digits', 'error');
            return;
        }

        try {
            const res = await fetch('/api/settings/security/pin', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({ pin })
            });
            const json = await res.json();
            if (!json.success) throw new Error(json.message);

            toast('Security PIN set successfully');
            closePinModal();
            document.getElementById('remove-pin-btn').style.display = 'block';
        } catch (e) {
            console.error(e);
            toast(e.message || 'Failed to set PIN', 'error');
        }
    }

    function openRemovePinModal() {
        document.getElementById('removePinModal').classList.add('active');
        document.getElementById('pin_remove_password').value = '';
    }

    async function removePin(event) {
        event.preventDefault();
        const password = document.getElementById('pin_remove_password').value;

        try {
            const res = await fetch('/api/settings/security/pin/remove', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({ current_password: password })
            });
            const json = await res.json();
            if (!json.success) throw new Error(json.message);

            toast('Security PIN removed');
            closePinModal();
            document.getElementById('remove-pin-btn').style.display = 'none';
        } catch (e) {
            console.error(e);
            toast(e.message || 'Failed to remove PIN', 'error');
        }
    }

    // Pattern Functions
    function openSetPatternModal() {
        document.getElementById('setPatternModal').classList.add('active');
        document.getElementById('pattern_input').value = '';
        document.getElementById('pattern_input').focus();
    }

    function closePatternModal() {
        document.getElementById('setPatternModal').classList.remove('active');
        document.getElementById('removePatternModal').classList.remove('active');
    }

    async function setPattern(event) {
        event.preventDefault();
        const pattern = document.getElementById('pattern_input').value;

        if (pattern.length < 4) {
            toast('Pattern must be at least 4 characters', 'error');
            return;
        }

        try {
            const res = await fetch('/api/settings/security/pattern', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({ pattern })
            });
            const json = await res.json();
            if (!json.success) throw new Error(json.message);

            toast('Security pattern set successfully');
            closePatternModal();
            document.getElementById('remove-pattern-btn').style.display = 'block';
        } catch (e) {
            console.error(e);
            toast(e.message || 'Failed to set pattern', 'error');
        }
    }

    function openRemovePatternModal() {
        document.getElementById('removePatternModal').classList.add('active');
        document.getElementById('pattern_remove_password').value = '';
    }

    async function removePattern(event) {
        event.preventDefault();
        const password = document.getElementById('pattern_remove_password').value;

        try {
            const res = await fetch('/api/settings/security/pattern/remove', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({ current_password: password })
            });
            const json = await res.json();
            if (!json.success) throw new Error(json.message);

            toast('Security pattern removed');
            closePatternModal();
            document.getElementById('remove-pattern-btn').style.display = 'none';
        } catch (e) {
            console.error(e);
            toast(e.message || 'Failed to remove pattern', 'error');
        }
    }

    // Device Functions
    async function loadDevices() {
        try {
            const res = await fetch('/api/settings/devices', { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            renderDevices(json.data || []);
        } catch (e) {
            console.error(e);
        }
    }

    function renderDevices(devices) {
        const list = document.getElementById('devicesList');
        if (!devices || devices.length === 0) {
            list.innerHTML = '<p style="text-align: center; color: var(--muted); padding: 20px;">No devices registered yet</p>';
            return;
        }

        list.innerHTML = devices.map(device => `
            <div class="device-item">
                <div class="device-info">
                    <div class="device-name"><i class="fas fa-${getDeviceIcon(device.type)}"></i> ${esc(device.name)}</div>
                    <div class="device-meta">
                        ${esc(device.type)} • IP: ${esc(device.ip)}<br>
                        Last active: ${new Date(device.last_activity).toLocaleDateString()}
                    </div>
                </div>
                <div class="device-actions">
                    <button class="btn ghost-danger" onclick="removeDevice('${device.id}')"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        `).join('');
    }

    function getDeviceIcon(type) {
        switch (type) {
            case 'mobile': return 'mobile-alt';
            case 'tablet': return 'tablet-alt';
            case 'desktop': return 'laptop';
            default: return 'globe';
        }
    }

    async function registerDevice() {
        const deviceName = prompt('Enter a name for this device (e.g., "My iPhone"):');
        if (!deviceName) return;

        try {
            const res = await fetch('/api/settings/devices/register', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({
                    device_name: deviceName,
                    device_type: /mobile|android|iphone/i.test(navigator.userAgent) ? 'mobile' : 'web'
                })
            });
            const json = await res.json();
            if (!json.success) throw new Error(json.message);

            toast('Device registered successfully');
            await loadDevices();
        } catch (e) {
            console.error(e);
            toast(e.message || 'Failed to register device', 'error');
        }
    }

    async function removeDevice(deviceId) {
        if (!confirm('Remove this device?')) return;

        try {
            const res = await fetch(`/api/settings/devices/${deviceId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                }
            });
            const json = await res.json();
            if (!json.success) throw new Error(json.message);

            toast('Device removed');
            await loadDevices();
        } catch (e) {
            console.error(e);
            toast(e.message || 'Failed to remove device', 'error');
        }
    }

    async function logoutOtherDevices() {
        if (!confirm('Logout from all other devices?')) return;

        try {
            const res = await fetch('/api/settings/logout-other-devices', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                }
            });
            const json = await res.json();
            if (!json.success) throw new Error(json.message);

            toast('Logged out from other devices');
            await loadDevices();
        } catch (e) {
            console.error(e);
            toast(e.message || 'Failed to logout', 'error');
        }
    }

    // Password Change
    async function changePassword(event) {
        event.preventDefault();
        
        const currentPassword = document.getElementById('current_password').value;
        const newPassword = document.getElementById('new_password').value;
        const passwordConfirmation = document.getElementById('password_confirmation').value;

        if (newPassword !== passwordConfirmation) {
            toast('Passwords do not match', 'error');
            return;
        }

        if (newPassword.length < 8) {
            toast('Password must be at least 8 characters', 'error');
            return;
        }

        try {
            const res = await fetch('/api/settings/password', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({
                    current_password: currentPassword,
                    password: newPassword,
                    password_confirmation: passwordConfirmation
                })
            });
            const json = await res.json();
            if (!json.success) throw new Error(json.message);

            toast('Password changed successfully');
            event.target.reset();
        } catch (e) {
            console.error(e);
            toast(e.message || 'Failed to change password', 'error');
        }
    }

    // Account Deletion
    function openDeleteAccountModal() {
        document.getElementById('deleteAccountModal').classList.add('active');
        document.getElementById('delete_password').value = '';
        document.getElementById('delete_confirmation').value = '';
    }

    function closeDeleteAccountModal() {
        document.getElementById('deleteAccountModal').classList.remove('active');
    }

    async function deleteAccount(event) {
        event.preventDefault();
        
        const password = document.getElementById('delete_password').value;
        const confirmation = document.getElementById('delete_confirmation').value;

        if (confirmation !== 'DELETE') {
            toast('Please type DELETE to confirm', 'error');
            return;
        }

        try {
            const res = await fetch('/api/settings/account/delete', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({
                    password,
                    confirmation
                })
            });
            const json = await res.json();
            if (!json.success) throw new Error(json.message);

            toast('Account deleted. Redirecting...');
            setTimeout(() => {
                window.location.href = json.redirect || '/account';
            }, 1500);
        } catch (e) {
            console.error(e);
            toast(e.message || 'Failed to delete account', 'error');
        }
    }

    async function logout() {
        if (!confirm('Logout?')) return;
        
        await fetch('/api/auth/logout', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        });
        window.location.href = '/account';
    }
</script>

</body>
</html>
