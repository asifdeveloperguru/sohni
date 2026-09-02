<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chat — Sohni</title>
    <link rel="icon" href="/images/app_logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://unpkg.com/tweetnacl@1.0.3/nacl-fast.min.js"></script>
    <script src="https://unpkg.com/tweetnacl-util@0.15.1/nacl-util.min.js"></script>
    <style>
        :root {
            --blue: #2563eb;
            --blue-dark: #1d4ed8;
            --cyan: #06b6d4;
            --navy: #0f172a;
            --ink: #1e293b;
            --muted: #64748b;
            --faint: #94a3b8;
            --line: #e2e8f0;
            --soft: #f1f5f9;
            --canvas: #f8fafc;
            --white: #fff;
            --green: #10b981;
            --danger: #ef4444;
            --r-sm: 10px;
            --r-md: 14px;
            --r-lg: 20px;
            --sh-sm: 0 1px 2px rgba(15,23,42,.06);
            --sh-md: 0 4px 12px rgba(15,23,42,.06), 0 1px 3px rgba(15,23,42,.04);
            --sh-lg: 0 12px 32px rgba(15,23,42,.1), 0 2px 8px rgba(15,23,42,.05);
        }
        * { box-sizing: border-box; }
        body { margin: 0; color: var(--ink); font-family: Inter, system-ui, sans-serif; background: var(--canvas); height: 100vh; overflow: hidden; -webkit-font-smoothing: antialiased; }
        button, input, select, textarea { font: inherit; }
        a { color: inherit; text-decoration: none; }

        .app { width: 100%; height: 100vh; display: flex; flex-direction: column; }

        /* ---------- Topbar ---------- */
        .topbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 12px 24px; border-bottom: 1px solid var(--line); background: var(--white); }
        .brand { display: inline-flex; align-items: center; gap: 9px; color: var(--navy); font: 600 15px Sora, sans-serif; }
        .brand:hover { color: var(--blue); }
        .brand i { font-size: 13px; }
        .top-actions { display: flex; gap: 8px; }
        .top-link { display: inline-flex; align-items: center; gap: 7px; padding: 8px 13px; border: 1px solid var(--line); border-radius: var(--r-sm); color: var(--ink); background: var(--white); cursor: pointer; font-size: 13px; font-weight: 500; transition: background .15s, border-color .15s; }
        .top-link:hover { background: var(--soft); border-color: #cbd5e1; }
        .top-link i { color: var(--faint); font-size: 13px; }
        .top-link.logout { color: var(--danger); }
        .top-link.logout i { color: var(--danger); }
        .top-link.logout:hover { background: #fef2f2; border-color: #fecaca; }

        .chat-wrapper { flex: 1; display: flex; flex-direction: column; overflow: hidden; }

        /* ---------- Chat header ---------- */
        .chat-header { position: relative; display: grid; grid-template-columns: auto minmax(0,1fr) auto; gap: 14px; align-items: center; padding: 12px 24px; border-bottom: 1px solid var(--line); background: var(--white); }
        .chat-header-photo { width: 44px; height: 44px; display: grid; place-items: center; overflow: hidden; border-radius: 50%; color: #fff; background: linear-gradient(135deg,var(--blue),var(--cyan)); font-size: 18px; }
        .chat-header-photo img { width: 100%; height: 100%; object-fit: cover; }
        .chat-header-info { min-width: 0; }
        .chat-header-info h1 { margin: 0; color: var(--navy); font: 600 16px Sora, sans-serif; letter-spacing: -.3px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .chat-header-info p { display: flex; align-items: center; gap: 6px; margin: 2px 0 0; color: var(--muted); font-size: 12.5px; }
        .chat-header-info .status { width: 7px; height: 7px; border-radius: 50%; background: var(--faint); }
        .chat-header-info .status.online { background: var(--green); box-shadow: 0 0 0 3px rgba(16,185,129,.16); }
        .chat-header-actions { display: flex; gap: 6px; }
        .chat-header-actions button { width: 36px; height: 36px; display: grid; place-items: center; border: 1px solid var(--line); border-radius: var(--r-sm); background: var(--white); color: var(--muted); cursor: pointer; font-size: 14px; transition: background .15s, color .15s, border-color .15s; }
        .chat-header-actions button:hover { background: var(--soft); color: var(--blue); border-color: #cbd5e1; }
        .chat-header-actions button.active { background: var(--blue); border-color: var(--blue); color: #fff; }

        /* ---------- Privacy panel ---------- */
        .panel-scrim { position: fixed; inset: 0; z-index: 25; background: rgba(15,23,42,.4); opacity: 0; visibility: hidden; transition: opacity .2s, visibility .2s; }
        .panel-scrim.open { opacity: 1; visibility: visible; }
        .side-panel { position: fixed; top: 0; right: 0; z-index: 26; width: min(360px, 100%); height: 100vh; display: flex; flex-direction: column; background: var(--white); border-left: 1px solid var(--line); box-shadow: -12px 0 40px rgba(15,23,42,.12); transform: translateX(100%); transition: transform .26s cubic-bezier(.32,.72,0,1); }
        .side-panel.open { transform: none; }
        .panel-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 20px; border-bottom: 1px solid var(--line); }
        .panel-head h2 { margin: 0; color: var(--navy); font: 600 16px Sora, sans-serif; }
        .panel-close { width: 32px; height: 32px; display: grid; place-items: center; border: 0; border-radius: var(--r-sm); background: var(--soft); color: var(--muted); cursor: pointer; font-size: 14px; }
        .panel-close:hover { background: #e2e8f0; color: var(--ink); }
        .panel-body { flex: 1; overflow-y: auto; padding: 20px; }
        .panel-identity { display: flex; flex-direction: column; align-items: center; gap: 10px; padding-bottom: 20px; margin-bottom: 20px; border-bottom: 1px solid var(--line); text-align: center; }
        .panel-identity .avatar { width: 72px; height: 72px; display: grid; place-items: center; overflow: hidden; border-radius: 50%; color: #fff; background: linear-gradient(135deg,var(--blue),var(--cyan)); font-size: 26px; }
        .panel-identity .avatar img { width: 100%; height: 100%; object-fit: cover; }
        .panel-identity strong { color: var(--navy); font: 600 16px Sora, sans-serif; }
        .panel-identity span { color: var(--muted); font-size: 12.5px; font-variant-numeric: tabular-nums; }
        .panel-section { margin-bottom: 22px; }
        .panel-section > h3 { margin: 0 0 10px; color: var(--faint); font-size: 11px; font-weight: 600; letter-spacing: .6px; text-transform: uppercase; }
        .panel-row { display: flex; align-items: center; gap: 12px; width: 100%; padding: 11px 12px; border: 1px solid var(--line); border-radius: var(--r-sm); background: var(--white); margin-bottom: 8px; text-align: left; }
        .panel-row > i { width: 18px; color: var(--faint); font-size: 14px; text-align: center; }
        .panel-row .label { flex: 1; min-width: 0; }
        .panel-row .label strong { display: block; color: var(--ink); font-size: 13.5px; font-weight: 500; }
        .panel-row .label small { display: block; margin-top: 2px; color: var(--muted); font-size: 11.5px; line-height: 1.4; }
        button.panel-row { cursor: pointer; transition: background .15s, border-color .15s; }
        button.panel-row:hover { background: var(--soft); border-color: #cbd5e1; }
        button.panel-row.danger { color: var(--danger); }
        button.panel-row.danger > i, button.panel-row.danger .label strong { color: var(--danger); }
        button.panel-row.danger:hover { background: #fef2f2; border-color: #fecaca; }
        .switch { position: relative; flex-shrink: 0; width: 38px; height: 22px; }
        .switch input { position: absolute; opacity: 0; width: 100%; height: 100%; margin: 0; cursor: pointer; }
        .switch span { position: absolute; inset: 0; border-radius: 999px; background: #cbd5e1; transition: background .18s; pointer-events: none; }
        .switch span::after { content: ''; position: absolute; top: 3px; left: 3px; width: 16px; height: 16px; border-radius: 50%; background: #fff; box-shadow: var(--sh-sm); transition: transform .18s; }
        .switch input:checked + span { background: var(--blue); }
        .switch input:checked + span::after { transform: translateX(16px); }
        .panel-note { display: flex; gap: 10px; padding: 12px; border-radius: var(--r-sm); background: #ecfdf5; color: #047857; font-size: 12px; line-height: 1.5; }
        .panel-note i { margin-top: 1px; }

        /* ---------- Messages ---------- */
        .chat-body { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .chat-messages { flex: 1; overflow-y: auto; padding: 24px; display: flex; flex-direction: column; gap: 10px; }
        .message-group { display: flex; gap: 10px; align-items: flex-end; max-width: min(720px, 78%); animation: slideIn .22s ease; }
        .message-group.own { flex-direction: row-reverse; align-self: flex-end; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }

        .message-avatar { width: 28px; height: 28px; min-width: 28px; display: grid; place-items: center; border-radius: 50%; background: linear-gradient(135deg,var(--blue),var(--cyan)); color: #fff; font-size: 11px; }
        .message-content { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
        .message-group.own .message-content { align-items: flex-end; }

        .message-bubble { padding: 10px 14px; border-radius: 16px 16px 16px 4px; background: var(--white); border: 1px solid var(--line); color: var(--ink); font-size: 14px; line-height: 1.5; overflow-wrap: anywhere; box-shadow: var(--sh-sm); }
        .message-bubble.own { border-radius: 16px 16px 4px 16px; border-color: transparent; background: var(--blue); color: #fff; }
        .message-bubble.locked { color: var(--muted); font-style: italic; }
        .message-bubble.own.locked { color: rgba(255,255,255,.75); }
        .message-time { padding: 0 4px; color: var(--faint); font-size: 11px; white-space: nowrap; }

        .message-bubble.file, .message-bubble.voice, .message-bubble.video, .message-bubble.media { display: flex; align-items: center; gap: 11px; }
        .media-meta { min-width: 0; }
        .media-meta strong { display: block; font-size: 13.5px; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .media-meta small { display: block; margin-top: 1px; font-size: 11.5px; opacity: .75; }
        .message-bubble.file a { font-weight: 500; text-decoration: underline; }
        .play-btn { width: 32px; height: 32px; display: grid; place-items: center; border: 0; border-radius: 50%; background: var(--soft); color: var(--blue); cursor: pointer; font-size: 12px; transition: background .15s; }
        .play-btn:hover { background: #e2e8f0; }
        .message-bubble.own .play-btn { background: rgba(255,255,255,.22); color: #fff; }
        .message-bubble.own .play-btn:hover { background: rgba(255,255,255,.32); }

        .empty-state { display: grid; place-items: center; margin: auto; text-align: center; color: var(--muted); padding: 40px 20px; }
        .empty-state i { margin-bottom: 14px; color: var(--faint); font-size: 36px; }
        .empty-state p { margin: 0; font-size: 14px; line-height: 1.7; }

        .key-warning { display: none; align-items: flex-start; gap: 10px; padding: 12px 24px; border-bottom: 1px solid #fde68a; background: #fffbeb; color: #92400e; font-size: 12.5px; line-height: 1.5; }
        .key-warning.active { display: flex; }
        .key-warning i { margin-top: 2px; }

        /* ---------- Composer ---------- */
        .chat-composer { padding: 14px 24px 18px; border-top: 1px solid var(--line); background: var(--white); }
        .composer-input { display: flex; flex-direction: column; gap: 10px; width: 100%; max-width: 1100px; margin: 0 auto; }
        .text-input-wrapper { display: flex; gap: 10px; align-items: flex-end; width: 100%; }
        .text-input { flex: 1; width: 100%; min-height: 44px; padding: 12px 16px; border: 1px solid var(--line); border-radius: var(--r-md); outline: 0; font-size: 14px; line-height: 1.5; resize: none; overflow-y: auto; max-height: 160px; background: var(--canvas); color: var(--ink); transition: border-color .15s, box-shadow .15s, background .15s; scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent; }
        .text-input:focus { border-color: var(--blue); background: var(--white); box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
        .text-input::placeholder { color: var(--faint); }
        .text-input::-webkit-scrollbar { width: 8px; }
        .text-input::-webkit-scrollbar-track { background: transparent; }
        .text-input::-webkit-scrollbar-thumb { border: 2px solid transparent; border-radius: 8px; background-clip: content-box; background-color: #cbd5e1; }
        .text-input::-webkit-scrollbar-thumb:hover { background-color: var(--faint); }

        .upload-progress { display: none; align-items: center; gap: 12px; padding: 10px 12px; border: 1px solid var(--line); border-radius: var(--r-sm); background: var(--canvas); }
        .upload-progress.active { display: flex; }
        .upload-progress .meta { flex: 1; min-width: 0; }
        .upload-progress .meta strong { display: block; color: var(--ink); font-size: 12.5px; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .upload-track { height: 5px; margin-top: 6px; border-radius: 999px; background: #e2e8f0; overflow: hidden; }
        .upload-fill { height: 100%; width: 0; border-radius: 999px; background: var(--blue); transition: width .2s; }
        .upload-progress .pct { color: var(--muted); font-size: 12px; font-variant-numeric: tabular-nums; }

        .media-buttons { display: flex; gap: 6px; }
        .media-btn { width: 34px; height: 34px; display: grid; place-items: center; border: 0; border-radius: var(--r-sm); background: transparent; color: var(--muted); cursor: pointer; font-size: 14px; transition: background .15s, color .15s; }
        .media-btn:hover { background: var(--soft); color: var(--blue); }
        .media-btn.active { background: #fef2f2; color: var(--danger); }

        .send-btn { width: 42px; height: 42px; display: grid; place-items: center; border: 0; border-radius: var(--r-md); background: var(--blue); color: #fff; cursor: pointer; font-size: 15px; transition: background .15s; }
        .send-btn:hover { background: var(--blue-dark); }
        .send-btn:disabled { background: #cbd5e1; cursor: not-allowed; }

        .recording-indicator { display: none; align-items: center; gap: 8px; padding: 0 4px; color: var(--danger); font-size: 12.5px; font-weight: 500; }
        .recording-indicator.active { display: flex; }
        .recording-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--danger); animation: pulse 1.4s infinite; }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: .3; } }

        input[type="file"] { display: none; }

        .toast { position: fixed; left: 50%; bottom: 24px; z-index: 40; padding: 11px 18px; border-radius: var(--r-sm); color: #fff; background: var(--navy); opacity: 0; transform: translate(-50%,10px); transition: .22s; pointer-events: none; font-size: 13.5px; box-shadow: var(--sh-lg); }
        .toast.show { opacity: 1; transform: translate(-50%,0); }

        @media (max-width: 768px) {
            .topbar { padding: 10px 16px; }
            .top-link span { display: none; }
            .chat-header { padding: 10px 16px; }
            .chat-messages { padding: 16px; }
            .chat-composer { padding: 12px 16px 16px; }
            .message-content { max-width: 78%; }
        }
        @media (max-width: 520px) {
            .chat-header-photo { width: 40px; height: 40px; font-size: 16px; }
            .chat-header-info h1 { font-size: 15px; }
            .message-content { max-width: 84%; }
            .side-panel { width: 100%; }
        }
    </style>
</head>
<body>
<div class="app">
    <header class="topbar">
        <a class="brand" href="/dashboard"><i class="fas fa-arrow-left"></i> Dashboard</a>
        <div class="top-actions"><a class="top-link" href="/profile"><i class="fas fa-user"></i><span>Profile</span></a><button class="top-link logout" onclick="logout()"><i class="fas fa-right-from-bracket"></i><span>Logout</span></button></div>
    </header>

    <div class="chat-wrapper">
        <section class="chat-header" id="chatHeader">
            <div class="chat-header-photo" id="chatPhoto"><i class="fas fa-user"></i></div>
            <div class="chat-header-info">
                <h1 id="chatName">Loading…</h1>
                <p><span class="status" id="chatStatusDot"></span><span id="chatStatus">Offline</span><span>·</span><span><i class="fas fa-lock" style="font-size:10px"></i> Encrypted</span></p>
            </div>
            <div class="chat-header-actions">
                <button title="Voice call" onclick="initiateVoiceCall()"><i class="fas fa-phone"></i></button>
                <button title="Video call" onclick="initiateVideoCall()"><i class="fas fa-video"></i></button>
                <button title="Privacy & settings" id="optionsBtn" onclick="toggleMoreOptions()"><i class="fas fa-ellipsis-v"></i></button>
            </div>
        </section>

        <div class="chat-body">
            <div class="key-warning" id="keyWarning">
                <i class="fas fa-triangle-exclamation"></i>
                <span><strong id="keyWarningName">This user</strong> has not opened Sohni since encryption was enabled, so there is no public key to encrypt to yet. Messages and files stay blocked until they sign in once.</span>
            </div>
            <div class="chat-messages" id="chatMessages">
                <div class="empty-state"><i class="fas fa-comments"></i><p>No messages yet<br>Start the conversation!</p></div>
            </div>

            <div class="chat-composer">
                <div class="composer-input">
                    <div class="upload-progress" id="uploadProgress">
                        <i class="fas fa-lock" style="color:var(--blue)"></i>
                        <span class="meta">
                            <strong id="uploadLabel">Encrypting…</strong>
                            <span class="upload-track"><span class="upload-fill" id="uploadFill"></span></span>
                        </span>
                        <span class="pct" id="uploadPercent">0%</span>
                    </div>
                    <div class="recording-indicator" id="recordingIndicator">
                        <div class="recording-dot"></div>
                        <span>Recording… tap the mic again to send</span>
                    </div>
                    <div class="text-input-wrapper">
                        <div class="media-buttons">
                            <button class="media-btn" title="Send file" onclick="triggerFileInput()"><i class="fas fa-paperclip"></i></button>
                            <button class="media-btn" id="voiceBtn" title="Record voice message" onclick="toggleVoiceRecording()"><i class="fas fa-microphone"></i></button>
                            <button class="media-btn" title="Send video" onclick="triggerVideoInput()"><i class="fas fa-video"></i></button>
                        </div>
                        <textarea class="text-input" id="messageInput" placeholder="Type a message…" rows="1"></textarea>
                        <button class="send-btn" onclick="sendMessage()" title="Send message"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="panel-scrim" id="panelScrim" onclick="closeMoreOptions()"></div>
<aside class="side-panel" id="optionsPanel" role="dialog" aria-label="Chat privacy settings">
    <div class="panel-head">
        <h2>Privacy &amp; settings</h2>
        <button class="panel-close" onclick="closeMoreOptions()" aria-label="Close"><i class="fas fa-xmark"></i></button>
    </div>
    <div class="panel-body">
        <div class="panel-identity">
            <div class="avatar" id="panelAvatar"><i class="fas fa-user"></i></div>
            <strong id="panelName">—</strong>
            <span id="panelId"></span>
        </div>

        <div class="panel-section">
            <h3>Privacy</h3>
            <label class="panel-row">
                <i class="fas fa-check-double"></i>
                <span class="label"><strong>Read receipts</strong><small>Let them see when you have read a message</small></span>
                <span class="switch"><input type="checkbox" id="prefReadReceipts" onchange="savePref('readReceipts', this.checked)"><span></span></span>
            </label>
            <label class="panel-row">
                <i class="fas fa-keyboard"></i>
                <span class="label"><strong>Typing indicator</strong><small>Show when you are typing a reply</small></span>
                <span class="switch"><input type="checkbox" id="prefTyping" onchange="savePref('typing', this.checked)"><span></span></span>
            </label>
            <label class="panel-row">
                <i class="fas fa-bell-slash"></i>
                <span class="label"><strong>Mute notifications</strong><small>Silence alerts from this conversation</small></span>
                <span class="switch"><input type="checkbox" id="prefMuted" onchange="savePref('muted', this.checked)"><span></span></span>
            </label>
            <label class="panel-row">
                <i class="fas fa-image"></i>
                <span class="label"><strong>Auto-download media</strong><small>Fetch files automatically over any network</small></span>
                <span class="switch"><input type="checkbox" id="prefAutoMedia" onchange="savePref('autoMedia', this.checked)"><span></span></span>
            </label>
            <p style="margin:8px 2px 0;color:var(--faint);font-size:11.5px;line-height:1.5;">These preferences are stored on this device only.</p>
        </div>

        <div class="panel-section">
            <h3>Security</h3>
            <div class="panel-note"><i class="fas fa-lock"></i><span>Messages are end-to-end encrypted. Sohni's servers store only ciphertext and cannot read this conversation.</span></div>
            <button class="panel-row" style="margin-top:10px" onclick="showSafetyNumber()">
                <i class="fas fa-fingerprint"></i>
                <span class="label"><strong>Verify safety number</strong><small>Confirm no one is intercepting this chat</small></span>
                <i class="fas fa-chevron-right" style="color:var(--faint);font-size:11px"></i>
            </button>
        </div>

        <div class="panel-section">
            <h3>Conversation</h3>
            <button class="panel-row" onclick="clearChatView()">
                <i class="fas fa-eraser"></i>
                <span class="label"><strong>Clear on this device</strong><small>Hide messages locally without deleting them</small></span>
            </button>
            <button class="panel-row danger" onclick="blockUser()">
                <i class="fas fa-ban"></i>
                <span class="label"><strong>Block user</strong><small>Stop all messages and calls from this person</small></span>
            </button>
            <button class="panel-row danger" onclick="reportUser()">
                <i class="fas fa-flag"></i>
                <span class="label"><strong>Report conversation</strong><small>Send this chat to moderation for review</small></span>
            </button>
        </div>
    </div>
</aside>

<input type="file" id="fileInput" onchange="sendFile(event)">
<input type="file" id="videoInput" accept="video/*" onchange="sendVideo(event)">

<div class="toast" id="toast"></div>

@include('partials.echo')
<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const MAX_FILE_BYTES = 2 * 1024 * 1024 * 1024; // 2 GB
    const CHUNK_SIZE = 4 * 1024 * 1024;            // 4 MB of plaintext per chunk
    let conversationId = null, userId = null, myUserId = null, messages = [], lastMessageId = 0;
    let messageTimer = null, voiceRecorder = null, isRecording = false, audioChunks = [], recordStartedAt = 0;
    let encryptionKeys = { publicKey: null, secretKey: null, theirPublicKey: null };

    // E2E Encryption utilities
    const crypto = {
        // The secret key stays in this browser; only the public key is ever uploaded.
        loadOrCreateKeyPair() {
            const stored = localStorage.getItem('sohni.identity');
            if (stored) {
                try {
                    const parsed = JSON.parse(stored);
                    return {
                        publicKey: nacl.util.decodeBase64(parsed.publicKey),
                        secretKey: nacl.util.decodeBase64(parsed.secretKey)
                    };
                } catch (e) { console.warn('Stored identity unreadable, regenerating'); }
            }
            const keypair = nacl.box.keyPair();
            localStorage.setItem('sohni.identity', JSON.stringify({
                publicKey: nacl.util.encodeBase64(keypair.publicKey),
                secretKey: nacl.util.encodeBase64(keypair.secretKey)
            }));
            return { publicKey: keypair.publicKey, secretKey: keypair.secretKey };
        },
        encryptMessage(message, theirPublicKey, mySecretKey) {
            const nonce = nacl.randomBytes(nacl.box.nonceLength);
            const encrypted = nacl.box(nacl.util.decodeUTF8(message), nonce, theirPublicKey, mySecretKey);
            const full = new Uint8Array(nonce.length + encrypted.length);
            full.set(nonce);
            full.set(encrypted, nonce.length);
            return nacl.util.encodeBase64(full);
        },
        decryptMessage(encryptedBase64, theirPublicKey, mySecretKey) {
            try {
                const full = nacl.util.decodeBase64(encryptedBase64);
                const nonce = full.slice(0, nacl.box.nonceLength);
                const decrypted = nacl.box.open(full.slice(nacl.box.nonceLength), nonce, theirPublicKey, mySecretKey);
                return decrypted ? nacl.util.encodeUTF8(decrypted) : null;
            } catch (e) {
                return null;
            }
        },
        // Files use a random symmetric key, which is then sealed to each recipient.
        sealKeyFor(fileKey, theirPublicKey, mySecretKey) {
            const nonce = nacl.randomBytes(nacl.box.nonceLength);
            const sealed = nacl.box(fileKey, nonce, theirPublicKey, mySecretKey);
            const full = new Uint8Array(nonce.length + sealed.length);
            full.set(nonce);
            full.set(sealed, nonce.length);
            return nacl.util.encodeBase64(full);
        },
        openSealedKey(sealedBase64, theirPublicKey, mySecretKey) {
            try {
                const full = nacl.util.decodeBase64(sealedBase64);
                const nonce = full.slice(0, nacl.box.nonceLength);
                return nacl.box.open(full.slice(nacl.box.nonceLength), nonce, theirPublicKey, mySecretKey);
            } catch (e) {
                return null;
            }
        },
        encryptChunk(bytes, fileKey) {
            const nonce = nacl.randomBytes(nacl.secretbox.nonceLength);
            const boxed = nacl.secretbox(bytes, nonce, fileKey);
            const frame = new Uint8Array(nonce.length + boxed.length);
            frame.set(nonce);
            frame.set(boxed, nonce.length);
            return frame;
        },
        decryptChunk(frame, fileKey) {
            const nonce = frame.slice(0, nacl.secretbox.nonceLength);
            return nacl.secretbox.open(frame.slice(nacl.secretbox.nonceLength), nonce, fileKey);
        }
    };

    document.addEventListener('DOMContentLoaded', async () => {
        const params = new URLSearchParams(window.location.search);
        conversationId = params.get('id');
        if (!conversationId) return location.href = '/dashboard';

        encryptionKeys = crypto.loadOrCreateKeyPair();
        await publishPublicKey();

        const textarea = document.getElementById('messageInput');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 160) + 'px';
        });
        textarea.addEventListener('keydown', e => {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
        });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMoreOptions(); });

        loadPrefs();
        await loadChatData();
        listenForCalls();
        await loadMessages();
        messageTimer = setInterval(loadMessages, 2000);
    });

    function listenForCalls() {
        if (!window.Echo || !myUserId) return;
        window.Echo.private(`user.${myUserId}`).listen('.incoming-call', payload => {
            if (confirm(`${payload.from.name} is calling. Join now?`)) {
                location.href = `/call?room=${payload.room_id}`;
            }
        });
    }

    async function publishPublicKey() {
        try {
            await fetch('/api/chat/keys', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ public_key: nacl.util.encodeBase64(encryptionKeys.publicKey) })
            });
        } catch (e) {
            console.warn('Could not publish public key', e);
        }
    }

    async function loadChatData() {
        try {
            const r = await fetch(`/api/chat/conversation/${conversationId}`, { headers: { Accept: 'application/json' } });
            const j = await r.json();
            if (!j.success) return showToast('Failed to load chat');

            const chat = j.data;
            userId = chat.user_id;
            myUserId = chat.me;
            document.getElementById('chatName').textContent = chat.name;
            document.getElementById('chatStatus').textContent = chat.is_online ? 'Online' : 'Offline';
            document.getElementById('chatStatusDot').classList.toggle('online', !!chat.is_online);

            document.getElementById('panelName').textContent = chat.name || '—';
            document.getElementById('panelId').textContent = chat.sohni_id || '';

            if (chat.avatar) {
                const img = `<img src="${escapeHtml(chat.avatar)}" alt="">`;
                document.getElementById('chatPhoto').innerHTML = img;
                document.getElementById('panelAvatar').innerHTML = img;
            }

            if (chat.encryption_key) {
                try { encryptionKeys.theirPublicKey = nacl.util.decodeBase64(chat.encryption_key); } catch (e) { console.warn('Bad public key'); }
            }

            if (!encryptionKeys.theirPublicKey) {
                document.getElementById('keyWarning').classList.add('active');
                document.getElementById('keyWarningName').textContent = chat.name || 'This user';
            } else {
                document.getElementById('keyWarning').classList.remove('active');
            }
        } catch (e) {
            showToast('Error loading chat');
        }
    }

    /* ---------- Privacy panel ---------- */
    const PREF_DEFAULTS = { readReceipts: true, typing: true, muted: false, autoMedia: true };

    function prefKey() { return `sohni.chat.${conversationId}.prefs`; }

    function loadPrefs() {
        let stored = {};
        try { stored = JSON.parse(localStorage.getItem(prefKey())) || {}; } catch (e) { stored = {}; }
        const prefs = { ...PREF_DEFAULTS, ...stored };
        document.getElementById('prefReadReceipts').checked = prefs.readReceipts;
        document.getElementById('prefTyping').checked = prefs.typing;
        document.getElementById('prefMuted').checked = prefs.muted;
        document.getElementById('prefAutoMedia').checked = prefs.autoMedia;
        return prefs;
    }

    function savePref(name, value) {
        let prefs = {};
        try { prefs = JSON.parse(localStorage.getItem(prefKey())) || {}; } catch (e) { prefs = {}; }
        prefs[name] = value;
        localStorage.setItem(prefKey(), JSON.stringify(prefs));
        showToast(value ? 'Setting enabled' : 'Setting disabled');
    }

    function toggleMoreOptions() {
        document.getElementById('optionsPanel').classList.contains('open') ? closeMoreOptions() : openMoreOptions();
    }

    function openMoreOptions() {
        loadPrefs();
        document.getElementById('optionsPanel').classList.add('open');
        document.getElementById('panelScrim').classList.add('open');
        document.getElementById('optionsBtn').classList.add('active');
    }

    function closeMoreOptions() {
        document.getElementById('optionsPanel').classList.remove('open');
        document.getElementById('panelScrim').classList.remove('open');
        document.getElementById('optionsBtn').classList.remove('active');
    }

    // Fingerprint of both public keys — matching values on each device means no interception.
    function showSafetyNumber() {
        if (!encryptionKeys.publicKey || !encryptionKeys.theirPublicKey) return showToast('Safety number unavailable — keys not exchanged');
        const combined = [...encryptionKeys.publicKey, ...encryptionKeys.theirPublicKey];
        let digits = '';
        for (let i = 0; i < combined.length && digits.length < 30; i += 3) digits += String(combined[i]).padStart(3, '0');
        showToast('Safety number: ' + (digits.slice(0, 30).match(/.{1,5}/g) || []).join(' '));
    }

    function clearChatView() {
        document.getElementById('chatMessages').innerHTML = '<div class="empty-state"><i class="fas fa-comments"></i><p>Cleared on this device<br>New messages will appear here.</p></div>';
        messages = [];
        closeMoreOptions();
        showToast('Chat cleared on this device');
    }

    async function blockUser() {
        if (!userId) return showToast('User not loaded');
        if (!confirm('Block this user? They will no longer be able to message or call you.')) return;
        try {
            const r = await fetch('/api/settings/blocked-users/add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ user_id: userId })
            });
            const j = await r.json();
            showToast(j.message || (j.success ? 'User blocked' : 'Could not block user'));
            if (j.success) setTimeout(() => location.href = '/dashboard', 900);
        } catch (e) {
            showToast('Could not reach the server');
        }
    }

    async function reportUser() {
        const reason = prompt('Why are you reporting this conversation? (spam, harassment, abuse, other)', 'other');
        if (reason === null) return;
        closeMoreOptions();
        try {
            const r = await fetch('/api/reports', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ reported_user_id: userId, conversation_id: conversationId, reason })
            });
            const j = await r.json();
            showToast(j.success ? 'Report submitted for review' : (j.message || 'Could not submit report'));
        } catch (e) {
            showToast('Could not reach the server');
        }
    }

    async function loadMessages() {
        try {
            const url = `/api/chat/messages/${conversationId}` + (lastMessageId ? `?after_id=${lastMessageId}` : '');
            const r = await fetch(url, { headers: { Accept: 'application/json' } });
            const j = await r.json();
            if (!j.success || !j.data.length) return;

            const box = document.getElementById('chatMessages');
            if (!messages.length) box.innerHTML = '';

            j.data.forEach(m => {
                lastMessageId = Math.max(lastMessageId, m.id);
                messages.push(m);
                box.insertAdjacentHTML('beforeend', renderMessage(m));
            });

            box.scrollTop = box.scrollHeight;
        } catch (e) {
            console.warn(e);
        }
    }

    function renderMessage(m) {
        let inner = '';
        let locked = false;

        if (m.message_type === 'text') {
            let text = m.body;

            if (m.is_encrypted || looksEncrypted(m.body)) {
                text = encryptionKeys.theirPublicKey
                    ? crypto.decryptMessage(m.body, encryptionKeys.theirPublicKey, encryptionKeys.secretKey)
                    : null;
                if (text === null) { text = 'Encrypted message — no key on this device'; locked = true; }
            }
            inner = escapeHtml(text);
        } else {
            const size = formatBytes(m.file_size);
            const label = m.message_type === 'voice'
                ? `Voice message${m.duration ? ' · ' + formatDuration(m.duration) : ''}`
                : escapeHtml(m.file_name || 'File');
            const icon = m.message_type === 'voice' ? 'fa-microphone' : m.message_type === 'video' ? 'fa-film' : 'fa-file';
            inner = `<button class="play-btn" onclick="downloadMedia(${Number(m.id)}, this)" title="Decrypt and open"><i class="fas ${icon}"></i></button>`
                + `<span class="media-meta"><strong>${label}</strong><small>${size}</small></span>`;
        }

        const stamp = m.date ? `${escapeHtml(m.date)} · ${escapeHtml(m.time)}` : escapeHtml(m.time);

        return `<div class="message-group ${m.is_own ? 'own' : ''}">`
            + (m.is_own ? '' : '<div class="message-avatar"><i class="fas fa-user"></i></div>')
            + '<div class="message-content">'
            + `<div class="message-bubble ${m.is_own ? 'own' : ''} ${m.message_type === 'text' ? '' : 'media'} ${locked ? 'locked' : ''}">${inner}</div>`
            + `<div class="message-time">${stamp}</div>`
            + '</div></div>';
    }

    // Legacy rows predate the is_encrypted column, so fall back to shape detection.
    function looksEncrypted(body) {
        return typeof body === 'string' && body.length > 40 && !body.includes(' ') && /^[A-Za-z0-9+/=]+$/.test(body);
    }

    function formatBytes(bytes) {
        if (!bytes) return '';
        const units = ['B', 'KB', 'MB', 'GB'];
        let i = 0, value = bytes;
        while (value >= 1024 && i < units.length - 1) { value /= 1024; i++; }
        return `${value.toFixed(value >= 10 || i === 0 ? 0 : 1)} ${units[i]}`;
    }

    function formatDuration(seconds) {
        const m = Math.floor(seconds / 60), s = seconds % 60;
        return `${m}:${String(s).padStart(2, '0')}`;
    }

    async function sendMessage() {
        const input = document.getElementById('messageInput');
        const body = input.value.trim();
        if (!body) return;

        if (!encryptionKeys.theirPublicKey) return showToast('Cannot encrypt yet — they have not published a key');

        input.value = '';
        input.style.height = 'auto';

        try {
            const payload = {
                conversation_id: conversationId,
                body: crypto.encryptMessage(body, encryptionKeys.theirPublicKey, encryptionKeys.secretKey),
                is_encrypted: true
            };
            const r = await fetch('/api/chat/messages', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify(payload)
            });
            const j = await r.json();
            if (!j.success) { input.value = body; showToast(j.message || 'Message failed'); return; }
            await loadMessages();
        } catch (e) {
            input.value = body;
            showToast('Error sending message');
        }
    }

    function sendFile(event) {
        const file = event.target.files[0];
        event.target.value = '';
        if (file) uploadEncrypted(file, 'file');
    }

    function sendVideo(event) {
        const file = event.target.files[0];
        event.target.value = '';
        if (file) uploadEncrypted(file, 'video');
    }

    /**
     * Encrypts the file in 4 MB chunks and streams them up, so a 2 GB transfer
     * never needs to sit in memory as one buffer.
     */
    async function uploadEncrypted(file, type, duration = null) {
        if (!encryptionKeys.theirPublicKey) return showToast('Cannot encrypt yet — they have not published a key');
        if (file.size > MAX_FILE_BYTES) return showToast(`Files must be 2 GB or smaller (this one is ${formatBytes(file.size)})`);

        const fileKey = nacl.randomBytes(nacl.secretbox.keyLength);
        const mediaKeys = {
            [String(myUserId)]: crypto.sealKeyFor(fileKey, encryptionKeys.publicKey, encryptionKeys.secretKey),
            [String(userId)]: crypto.sealKeyFor(fileKey, encryptionKeys.theirPublicKey, encryptionKeys.secretKey)
        };

        showProgress(`Encrypting ${file.name}`, 0);

        try {
            const initRes = await fetch('/api/chat/upload/init', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ conversation_id: conversationId, file_name: file.name, file_size: file.size })
            });
            const init = await initRes.json();
            if (!init.success) throw new Error(init.message || 'Upload rejected');

            const uploadId = init.data.upload_id;
            const total = Math.max(1, Math.ceil(file.size / CHUNK_SIZE));

            for (let index = 0; index < total; index++) {
                const slice = file.slice(index * CHUNK_SIZE, (index + 1) * CHUNK_SIZE);
                const plain = new Uint8Array(await slice.arrayBuffer());
                const frame = crypto.encryptChunk(plain, fileKey);

                const form = new FormData();
                form.append('upload_id', uploadId);
                form.append('index', index);
                form.append('chunk', new Blob([frame]), 'chunk');

                const res = await fetch('/api/chat/upload/chunk', {
                    method: 'POST',
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: form
                });
                const chunkJson = await res.json();
                if (!chunkJson.success) throw new Error(chunkJson.message || 'Chunk rejected');

                showProgress(`Sending ${file.name}`, Math.round(((index + 1) / total) * 100));
            }

            const completeRes = await fetch('/api/chat/upload/complete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({
                    upload_id: uploadId,
                    type,
                    file_name: file.name,
                    mime_type: file.type || 'application/octet-stream',
                    duration,
                    media_keys: mediaKeys
                })
            });
            const complete = await completeRes.json();
            if (!complete.success) throw new Error(complete.message || 'Could not finish upload');

            hideProgress();
            await loadMessages();
        } catch (e) {
            hideProgress();
            showToast(e.message || 'Upload failed');
        }
    }

    async function downloadMedia(messageId, button) {
        const m = messages.find(x => x.id === messageId);
        if (!m) return;
        if (!m.media_key) return showToast('No key for this file on this device');
        if (!encryptionKeys.theirPublicKey) return showToast('Encryption keys unavailable');

        const senderKey = m.is_own ? encryptionKeys.publicKey : encryptionKeys.theirPublicKey;
        const fileKey = crypto.openSealedKey(m.media_key, senderKey, encryptionKeys.secretKey);
        if (!fileKey) return showToast('Could not unseal this file');

        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        try {
            const res = await fetch(m.file_url, { headers: { Accept: 'application/octet-stream' } });
            if (!res.ok) throw new Error('Download failed');
            const cipher = new Uint8Array(await res.arrayBuffer());

            const frameSize = nacl.secretbox.nonceLength + CHUNK_SIZE + nacl.secretbox.overheadLength;
            const parts = [];
            for (let offset = 0; offset < cipher.length; offset += frameSize) {
                const plain = crypto.decryptChunk(cipher.slice(offset, offset + frameSize), fileKey);
                if (!plain) throw new Error('Decryption failed');
                parts.push(plain);
            }

            const url = URL.createObjectURL(new Blob(parts, { type: m.mime_type || 'application/octet-stream' }));
            const link = document.createElement('a');
            link.href = url;
            link.download = m.file_name || 'sohni-file';
            link.click();
            setTimeout(() => URL.revokeObjectURL(url), 10000);
            showToast('Decrypted and saved');
        } catch (e) {
            showToast(e.message || 'Could not open file');
        } finally {
            const icon = m.message_type === 'voice' ? 'fa-microphone' : m.message_type === 'video' ? 'fa-film' : 'fa-file';
            button.innerHTML = `<i class="fas ${icon}"></i>`;
        }
    }

    function showProgress(label, percent) {
        const bar = document.getElementById('uploadProgress');
        bar.classList.add('active');
        document.getElementById('uploadLabel').textContent = label;
        document.getElementById('uploadPercent').textContent = percent + '%';
        document.getElementById('uploadFill').style.width = percent + '%';
    }

    function hideProgress() {
        document.getElementById('uploadProgress').classList.remove('active');
        document.getElementById('uploadFill').style.width = '0%';
    }

    async function toggleVoiceRecording() {
        if (isRecording) {
            stopVoiceRecording();
        } else {
            startVoiceRecording();
        }
    }

    async function startVoiceRecording() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            voiceRecorder = new MediaRecorder(stream);
            audioChunks = [];
            isRecording = true;
            recordStartedAt = Date.now();

            document.getElementById('voiceBtn').classList.add('active');
            document.getElementById('recordingIndicator').classList.add('active');

            voiceRecorder.ondataavailable = (e) => audioChunks.push(e.data);
            voiceRecorder.onstop = async () => {
                stream.getTracks().forEach(t => t.stop());
                const seconds = Math.round((Date.now() - recordStartedAt) / 1000);
                const blob = new Blob(audioChunks, { type: 'audio/webm' });
                const file = new File([blob], `voice-${Date.now()}.webm`, { type: 'audio/webm' });

                isRecording = false;
                document.getElementById('voiceBtn').classList.remove('active');
                document.getElementById('recordingIndicator').classList.remove('active');

                if (blob.size) await uploadEncrypted(file, 'voice', seconds);
            };
            voiceRecorder.start();
        } catch (e) {
            showToast('Microphone access denied');
        }
    }

    function stopVoiceRecording() {
        if (voiceRecorder && voiceRecorder.state !== 'inactive') voiceRecorder.stop();
    }

    function triggerFileInput() {
        document.getElementById('fileInput').click();
    }

    function triggerVideoInput() {
        document.getElementById('videoInput').click();
    }

    function initiateVoiceCall() {
        startCall('audio');
    }

    function initiateVideoCall() {
        startCall('video');
    }

    async function startCall(mode) {
        if (!userId) return showToast('Chat still loading');

        try {
            const res = await fetch('/api/calls', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ conversation_id: Number(conversationId), mode, user_ids: [userId] })
            });
            const json = await res.json();
            if (!json.success) return showToast(json.message || 'Could not start the call');
            location.href = `/call?room=${json.data.room_id}&join=1`;
        } catch (e) {
            showToast('Could not reach the server');
        }
    }

    function showToast(message) {
        const t = document.getElementById('toast');
        t.textContent = message;
        t.classList.add('show');
        clearTimeout(window.toastTimer);
        window.toastTimer = setTimeout(() => t.classList.remove('show'), 2500);
    }

    async function logout() {
        await fetch('/api/auth/logout', { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': CSRF } });
        location.href = '/account';
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c]));
    }

    window.addEventListener('beforeunload', () => {
        if (messageTimer) clearInterval(messageTimer);
    });
</script>
</body>
</html>
