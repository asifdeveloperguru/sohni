<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sohni — Dashboard</title>
    <link rel="icon" href="/images/app_logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
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
            --amber: #f59e0b;
            --danger: #ef4444;
            --r-sm: 10px;
            --r-md: 14px;
            --r-lg: 20px;
            --sh-sm: 0 1px 2px rgba(15,23,42,.06);
            --sh-md: 0 4px 12px rgba(15,23,42,.06), 0 1px 3px rgba(15,23,42,.04);
            --sh-lg: 0 12px 32px rgba(15,23,42,.08), 0 2px 8px rgba(15,23,42,.04);
        }
        * { box-sizing: border-box; }
        body { margin: 0; color: var(--ink); font-family: Inter, system-ui, sans-serif; background: var(--canvas); -webkit-font-smoothing: antialiased; }
        button, input, select, textarea { font: inherit; }
        a { color: inherit; text-decoration: none; }
        ::selection { background: rgba(37,99,235,.18); }

        .app { width: 100%; max-width: 1440px; min-height: 100vh; margin: 0 auto; padding: 0 24px 56px; }

        /* ---------- Topbar ---------- */
        .topbar { position: sticky; top: 0; z-index: 20; display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 14px 0; margin-bottom: 24px; background: rgba(248,250,252,.85); backdrop-filter: blur(12px); border-bottom: 1px solid var(--line); }
        .brand { display: flex; align-items: center; gap: 10px; color: var(--navy); font: 700 19px Sora, sans-serif; letter-spacing: -.4px; }
        .brand img { width: 32px; height: 32px; border-radius: 8px; }
        .top-actions { display: flex; gap: 8px; align-items: center; }
        .top-link, .soft-btn { display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 9px 14px; border: 1px solid var(--line); border-radius: var(--r-sm); color: var(--ink); background: var(--white); cursor: pointer; font-size: 13px; font-weight: 500; transition: background .15s, border-color .15s, color .15s; }
        .top-link:hover, .soft-btn:hover { background: var(--soft); border-color: #cbd5e1; }
        .top-link i { color: var(--faint); font-size: 13px; }
        .top-link:hover i { color: var(--blue); }
        .top-link.logout { color: var(--danger); }
        .top-link.logout i { color: var(--danger); }
        .top-link.logout:hover { background: #fef2f2; border-color: #fecaca; }

        /* ---------- Hero ---------- */
        .profile-banner { display: grid; grid-template-columns: auto minmax(0,1fr) auto; align-items: center; gap: 24px; padding: 24px 28px; border: 1px solid var(--line); border-radius: var(--r-lg); background: var(--white); box-shadow: var(--sh-md); }
        .banner-photo { position: relative; width: 84px; height: 84px; display: grid; place-items: center; overflow: hidden; border-radius: 50%; color: #fff; background: linear-gradient(135deg, var(--blue), var(--cyan)); font-size: 30px; }
        .banner-photo img { width: 100%; height: 100%; object-fit: cover; }
        .banner-main { min-width: 0; }
        .banner-main h1 { margin: 0 0 4px; color: var(--navy); font: 700 clamp(20px,2.2vw,26px) Sora, sans-serif; letter-spacing: -.6px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .banner-main p { margin: 0 0 12px; color: var(--muted); font-size: 14px; line-height: 1.5; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .id-pills { display: flex; gap: 8px; flex-wrap: wrap; }
        .id-pill { display: inline-flex; align-items: center; gap: 6px; padding: 5px 11px; border: 1px solid var(--line); border-radius: 999px; color: var(--muted); background: var(--soft); font-size: 12px; font-weight: 500; font-variant-numeric: tabular-nums; }
        .id-pill i { font-size: 11px; color: var(--faint); }
        .id-pill.verified { color: var(--green); background: #ecfdf5; border-color: #a7f3d0; }
        .id-pill.verified i { color: var(--green); }

        .banner-side { display: flex; flex-direction: column; align-items: flex-end; gap: 16px; }
        .banner-stats { display: flex; gap: 8px; }
        .banner-stat { min-width: 78px; padding: 10px 14px; border: 1px solid var(--line); border-radius: var(--r-md); background: var(--white); text-align: center; }
        .banner-stat strong { display: block; color: var(--navy); font: 700 20px Sora, sans-serif; line-height: 1.1; font-variant-numeric: tabular-nums; }
        .banner-stat span { color: var(--faint); font-size: 11px; font-weight: 500; }
        .banner-actions { display: flex; gap: 8px; }
        .banner-actions .top-link { padding: 8px 12px; font-size: 12px; }

        /* ---------- Tabs ---------- */
        .panel-nav { display: flex; gap: 4px; margin: 24px 0 20px; padding: 5px; border: 1px solid var(--line); border-radius: var(--r-md); background: var(--white); box-shadow: var(--sh-sm); overflow-x: auto; scrollbar-width: none; }
        .panel-nav::-webkit-scrollbar { display: none; }
        .panel-tab { flex: 1; min-width: 104px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 14px; border: 0; border-radius: var(--r-sm); color: var(--muted); background: transparent; cursor: pointer; font-size: 13px; font-weight: 500; white-space: nowrap; transition: background .15s, color .15s; }
        .panel-tab i { font-size: 15px; }
        .panel-tab:hover { color: var(--ink); background: var(--soft); }
        .panel-tab.active { color: var(--white); background: var(--blue); font-weight: 600; box-shadow: var(--sh-sm); }
        .panel-tab.active:hover { background: var(--blue-dark); }

        /* ---------- Panels ---------- */
        .sub-panel { display: none; padding: 24px; border: 1px solid var(--line); border-radius: var(--r-lg); background: var(--white); box-shadow: var(--sh-md); }
        .sub-panel.active { display: block; }
        .toolbar { display: flex; gap: 10px; margin-bottom: 20px; align-items: center; }
        .search { display: flex; align-items: center; gap: 10px; flex: 1; padding: 0 14px; border: 1px solid var(--line); border-radius: var(--r-sm); background: var(--canvas); transition: border-color .15s, box-shadow .15s; }
        .search:focus-within { border-color: var(--blue); background: var(--white); box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
        .search i { color: var(--faint); font-size: 14px; }
        .search input { width: 100%; padding: 10px 0; border: 0; outline: 0; color: var(--ink); font-size: 14px; background: transparent; }
        .search input::placeholder { color: var(--faint); }
        .filter { min-width: 140px; padding: 10px 12px; border: 1px solid var(--line); border-radius: var(--r-sm); outline: 0; color: var(--ink); background: var(--white); font-size: 13px; cursor: pointer; transition: border-color .15s, box-shadow .15s; }
        .filter:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
        .section-title { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin: 0 0 20px; }
        .section-title h2 { margin: 0; color: var(--navy); font: 700 20px Sora, sans-serif; letter-spacing: -.4px; }
        .section-title p { margin: 3px 0 0; color: var(--muted); font-size: 13px; }
        .count { padding: 5px 12px; border-radius: 999px; color: var(--muted); background: var(--soft); font-size: 12px; font-weight: 600; white-space: nowrap; }

        /* ---------- Cards ---------- */
        .user-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(232px, 1fr)); gap: 16px; }
        .user-card { position: relative; min-width: 0; padding: 22px 18px 18px; border: 1px solid var(--line); border-radius: var(--r-md); background: var(--white); text-align: center; cursor: pointer; animation: appear .3s both; transition: border-color .18s, box-shadow .18s, transform .18s; }
        .user-card:hover { border-color: #cbd5e1; box-shadow: var(--sh-lg); transform: translateY(-3px); }
        .user-avatar { width: 72px; height: 72px; display: grid; place-items: center; overflow: hidden; margin: 0 auto 14px; border-radius: 50%; color: #fff; background: linear-gradient(135deg, var(--blue), var(--cyan)); font-size: 26px; }
        .user-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .user-card h3 { margin: 0 0 4px; overflow: hidden; color: var(--navy); font: 600 15px Sora, sans-serif; text-overflow: ellipsis; white-space: nowrap; }
        .user-card small { display: block; overflow: hidden; margin-bottom: 14px; color: var(--muted); font-size: 12.5px; text-overflow: ellipsis; white-space: nowrap; font-variant-numeric: tabular-nums; }
        .verify { position: absolute; top: 12px; right: 12px; width: 22px; height: 22px; display: grid; place-items: center; border-radius: 50%; background: #ecfdf5; color: var(--green); font-size: 11px; }
        .notification { position: absolute; top: 12px; left: 12px; min-width: 20px; height: 20px; display: grid; place-items: center; padding: 0 6px; border-radius: 999px; color: #fff; background: var(--danger); font-size: 11px; font-weight: 600; }
        .follow { width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 9px 12px; border: 0; border-radius: var(--r-sm); color: #fff; background: var(--blue); cursor: pointer; font-size: 13px; font-weight: 600; transition: background .15s; }
        .follow:hover { background: var(--blue-dark); }
        .follow i { font-size: 12px; }

        .empty { grid-column: 1 / -1; padding: 56px 24px; border: 1px dashed var(--line); border-radius: var(--r-md); color: var(--muted); background: var(--canvas); text-align: center; font-size: 14px; line-height: 1.7; }
        .empty i { display: block; margin-bottom: 12px; color: var(--faint); font-size: 32px; }

        .profile-card { display: grid; grid-template-columns: auto 1fr auto; gap: 18px; align-items: center; padding: 20px; border: 1px solid var(--line); border-radius: var(--r-md); background: var(--white); }
        .profile-card-photo { width: 72px; height: 72px; display: grid; place-items: center; overflow: hidden; border-radius: 50%; color: #fff; background: linear-gradient(135deg,var(--blue),var(--cyan)); font-size: 26px; }
        .profile-card-photo img { width: 100%; height: 100%; object-fit: cover; }
        .profile-card h2 { margin: 0 0 4px; color: var(--navy); font: 600 17px Sora, sans-serif; }
        .profile-card p { margin: 0; color: var(--muted); font-size: 13px; line-height: 1.6; }
        .card-actions { display: flex; gap: 8px; }
        .card-actions button { padding: 8px 14px; border: 1px solid var(--line); border-radius: var(--r-sm); color: var(--ink); background: var(--white); cursor: pointer; font-size: 13px; font-weight: 500; transition: background .15s, border-color .15s; }
        .card-actions button:hover { background: var(--soft); border-color: #cbd5e1; }
        .card-actions .block { color: var(--danger); }
        .card-actions .block:hover { background: #fef2f2; border-color: #fecaca; }

        /* ---------- Legacy inline chat (hidden) ---------- */
        .chat-layout { display: grid; grid-template-columns: 1fr; }
        .chat-user { display: none; }
        .chat-room { display: none; flex-direction: column; min-width: 0; }
        .chat-room.active { display: flex; margin-top: 16px; border: 1px solid var(--line); border-radius: var(--r-md); overflow: hidden; min-height: 440px; }
        .chat-room-head { padding: 16px 20px; border-bottom: 1px solid var(--line); color: var(--navy); font: 600 15px Sora, sans-serif; }
        .chat-messages { flex: 1; overflow: auto; padding: 20px; background: var(--canvas); }
        .bubble { width: fit-content; max-width: 70%; margin-bottom: 10px; padding: 10px 14px; border-radius: var(--r-md); background: var(--white); border: 1px solid var(--line); font-size: 13.5px; }
        .bubble.own { margin-left: auto; color: #fff; border: 0; background: var(--blue); }
        .composer { display: none; gap: 8px; padding: 12px; border-top: 1px solid var(--line); }
        .chat-room.active .composer { display: flex; }
        .composer input { flex: 1; padding: 10px 14px; border: 1px solid var(--line); border-radius: var(--r-sm); outline: 0; background: var(--canvas); font-size: 13.5px; }
        .send { width: 42px; border: 0; border-radius: var(--r-sm); color: #fff; background: var(--blue); cursor: pointer; }

        /* ---------- Modals ---------- */
        .modal { display: none; position: fixed; inset: 0; z-index: 30; place-items: center; padding: 20px; background: rgba(15,23,42,.55); backdrop-filter: blur(4px); }
        .modal.open { display: grid; animation: fade .18s ease; }
        .modal-box { width: min(440px,100%); padding: 26px; border-radius: var(--r-lg); background: var(--white); box-shadow: 0 24px 64px rgba(15,23,42,.24); animation: pop .22s cubic-bezier(.2,.9,.3,1.2); }
        .modal-box h2 { margin: 0 0 6px; color: var(--navy); font: 700 18px Sora, sans-serif; letter-spacing: -.3px; }
        .modal-box p { margin: 0 0 18px; color: var(--muted); font-size: 13.5px; line-height: 1.6; }
        .modal-box input { width: 100%; padding: 11px 14px; border: 1px solid var(--line); border-radius: var(--r-sm); outline: 0; font-size: 14px; background: var(--canvas); }
        .modal-box input:focus { border-color: var(--blue); background: var(--white); box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
        .modal-actions { display: flex; gap: 10px; margin-top: 18px; }
        .modal-actions button { flex: 1; padding: 11px; border: 1px solid var(--line); border-radius: var(--r-sm); color: var(--ink); background: var(--white); cursor: pointer; font-size: 13.5px; font-weight: 600; transition: background .15s; }
        .modal-actions button:hover { background: var(--soft); }
        .modal-actions .primary { color: #fff; border-color: var(--blue); background: var(--blue); }
        .modal-actions .primary:hover { background: var(--blue-dark); }
        #profileQr { display: grid; place-items: center; padding: 20px; margin: 4px 0; border-radius: var(--r-md); background: var(--canvas); }
        #qrReader { width: 100%; min-height: 280px; overflow: hidden; border-radius: var(--r-md); background: var(--navy); }
        #qrReader video { width: 100% !important; height: auto !important; display: block; }

        .toast { position: fixed; left: 50%; bottom: 24px; z-index: 40; padding: 11px 18px; border-radius: var(--r-sm); color: #fff; background: var(--navy); opacity: 0; transform: translate(-50%,10px); transition: .22s; pointer-events: none; font-size: 13.5px; box-shadow: var(--sh-lg); }
        .toast.show { opacity: 1; transform: translate(-50%,0); }
        .fab { position: fixed; right: 28px; bottom: 28px; width: 54px; height: 54px; display: grid; place-items: center; border: 0; border-radius: 50%; color: #fff; background: var(--blue); box-shadow: 0 8px 24px rgba(37,99,235,.36); cursor: pointer; font-size: 18px; z-index: 10; transition: background .15s, transform .15s; }
        .fab:hover { background: var(--blue-dark); transform: scale(1.06); }

        @keyframes appear { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
        @keyframes fade { from { opacity: 0; } to { opacity: 1; } }
        @keyframes pop { from { opacity: 0; transform: translateY(12px) scale(.97); } to { opacity: 1; transform: none; } }

        /* ---------- Responsive ---------- */
        @media (max-width: 900px) {
            .app { padding: 0 16px 48px; }
            .profile-banner { grid-template-columns: auto minmax(0,1fr); gap: 16px; padding: 20px; }
            .banner-side { grid-column: 1 / -1; align-items: stretch; }
            .banner-stats { justify-content: space-between; }
            .banner-stat { flex: 1; min-width: 0; }
            .banner-actions { flex-wrap: wrap; }
            .banner-actions .top-link { flex: 1; }
            .panel-tab { flex: 0 0 auto; min-width: auto; }
            .panel-tab span { display: none; }
            .panel-tab i { font-size: 17px; }
            .panel-tab { padding: 11px 16px; }
            .sub-panel { padding: 18px; }
        }
        @media (max-width: 620px) {
            .topbar { padding: 12px 0; }
            .top-link span { display: none; }
            .top-link { padding: 9px 11px; }
            .banner-photo { width: 64px; height: 64px; font-size: 24px; }
            .banner-main h1 { font-size: 19px; }
            .banner-main p { font-size: 13px; white-space: normal; }
            .toolbar { flex-direction: column; align-items: stretch; }
            .filter { width: 100%; }
            .user-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; }
            .user-avatar { width: 60px; height: 60px; font-size: 22px; }
            .profile-card { grid-template-columns: 1fr; text-align: center; }
            .profile-card-photo { margin: 0 auto; }
            .card-actions { justify-content: center; }
            .fab { right: 18px; bottom: 18px; }
        }
    </style>
</head>
<body>
<div class="app">
    
    <header class="topbar">
        <a class="brand" href="/dashboard"><img src="/images/app_logo.png" alt="Sohni">Sohni</a>
        <div class="top-actions"><a class="top-link" href="/profile"><i class="fas fa-user"></i><span>Profile</span></a><a class="top-link" href="/settings"><i class="fas fa-gear"></i><span>Settings</span></a><button class="top-link logout" onclick="logout()"><i class="fas fa-right-from-bracket"></i><span>Logout</span></button></div>
    </header>
    <section class="profile-banner">
        <div class="banner-photo" id="bannerPhoto"><i class="fas fa-user"></i></div>
        <div class="banner-main">
            <h1 id="profileName">Welcome back</h1>
            <p id="profileAbout">Your community is waiting for you.</p>
            <div class="id-pills">
                <span class="id-pill" id="profileId"><i class="fas fa-id-card"></i> ID loading…</span>
                <span class="id-pill verified"><i class="fas fa-circle-check"></i> <span id="profileLevel">Verified</span></span>
            </div>
        </div>
        <div class="banner-side">
            <div class="banner-stats">
                <div class="banner-stat"><strong id="friendCount">0</strong><span>Friends</span></div>
                <div class="banner-stat"><strong id="followerCount">0</strong><span>Followers</span></div>
                <div class="banner-stat"><strong id="groupCount">0</strong><span>Groups</span></div>
            </div>
            <div class="banner-actions">
                <a class="top-link" href="/profile"><i class="fas fa-id-card"></i> Profile</a>
                <a class="top-link" href="/settings"><i class="fas fa-gear"></i> Settings</a>
                <button class="top-link" onclick="openProfileQr()"><i class="fas fa-qrcode"></i> QR</button>
                <button class="top-link" onclick="openScanner()"><i class="fas fa-camera"></i> Scan</button>
            </div>
        </div>
    </section>
    <nav class="panel-nav"><button class="panel-tab active" onclick="showPanel('friends',this)"><i class="fas fa-user-group"></i><span>Friends</span></button><button class="panel-tab" onclick="showPanel('chat',this)"><i class="fas fa-comments"></i><span>Chat</span></button><button class="panel-tab" onclick="showPanel('groups',this)"><i class="fas fa-users"></i><span>Groups</span></button><button class="panel-tab" onclick="showPanel('followers',this)"><i class="fas fa-star"></i><span>Followers</span></button><button class="panel-tab" onclick="showPanel('calls',this)"><i class="fas fa-phone"></i><span>Calls</span></button><button class="panel-tab" onclick="showPanel('stories',this)"><i class="fas fa-circle-play"></i><span>Stories</span></button></nav>
    <main class="content-panel">
        <section class="sub-panel active" id="panel-friends"><div class="toolbar"><label class="search"><i class="fas fa-search"></i><input id="peopleSearch" type="search" placeholder="Search your friends by name or ID…" oninput="renderPeople()"></label><select class="filter" id="peopleFilter" onchange="renderPeople()"><option value="all">All levels</option><option value="free">Free</option><option value="premium">Premium</option></select><select class="filter" id="statusFilter" onchange="renderPeople()"><option value="all">All status</option><option value="online">Online</option></select></div><div class="section-title"><div><h2>Friends list</h2><p>Only friends who accepted the connection are shown</p></div><span class="count" id="peopleCount">0 friends</span></div><div class="user-grid" id="peopleGrid"><div class="empty"><i class="fas fa-spinner fa-spin"></i>Loading friends…</div></div></section>
        <section class="sub-panel" id="panel-chat"><div class="section-title"><div><h2>Messages</h2><p>Continue your conversations</p></div></div><div class="user-grid" id="chatList"><div class="empty"><i class="fas fa-comments"></i>No chats yet. Start chatting with a friend.</div></div><div class="chat-layout"><div class="chat-room"><div class="chat-room-head" id="chatTitle">Select a conversation</div><div class="chat-messages" id="chatMessages"><div class="empty">Loading chat…</div></div><form class="composer" onsubmit="sendMessage(event)"><input id="messageInput" placeholder="Write a message…"><button class="send" type="submit"><i class="fas fa-paper-plane"></i></button></form></div></div></section>
        <section class="sub-panel" id="panel-groups"><div class="section-title"><div><h2>Groups</h2><p>Join and manage your communities</p></div><span class="count" id="groupsCount">0 groups</span></div><div class="user-grid" id="groupsList"><div class="empty"><i class="fas fa-users"></i>No groups yet. Groups are coming soon.</div></div></section>
        <section class="sub-panel" id="panel-followers"><div class="toolbar"><label class="search"><i class="fas fa-search"></i><input placeholder="Search followers…" oninput="filterCards(this,'followersGrid')"></label><select class="filter"><option>Date joined</option><option>Level</option><option>Status</option></select></div><div class="section-title"><div><h2>Followers</h2><p>People who follow your profile.</p></div></div><div class="user-grid" id="followersGrid"><div class="empty"><i class="fas fa-star"></i>No followers yet.</div></div></section>
        <section class="sub-panel" id="panel-calls"><div class="empty"><i class="fas fa-phone"></i>No calls yet.</div></section>
        <section class="sub-panel" id="panel-stories"><div class="empty"><i class="fas fa-circle-play"></i>Stories are coming soon.</div></section>
        <section class="sub-panel" id="panel-user"><div id="userProfileCard"></div></section>
    </main>
</div>
<button class="fab" onclick="openRequestModal()" title="Send connection request"><i class="fas fa-plus"></i></button>
<div class="modal" id="requestModal"><div class="modal-box"><h2>Connect with someone</h2><p>Enter their 14-digit Sohni ID.</p><input id="requestIdentifier" inputmode="numeric" maxlength="14" placeholder="14-digit Sohni ID"><div class="modal-actions"><button onclick="closeRequestModal()">Cancel</button><button class="primary" onclick="sendRequest()">Send request</button></div></div></div><div class="toast" id="toast"></div>
<div class="modal" id="qrModal"><div class="modal-box"><h2>My Sohni QR code</h2><p>Share this code so friends can scan and connect with you.</p><div id="profileQr"></div><div class="modal-actions"><button onclick="closeQrModal()">Close</button><button class="primary" onclick="downloadProfileQr()">Download QR</button></div></div></div>
<div class="modal" id="scannerModal"><div class="modal-box"><h2>Scan profile QR</h2><p>Scan a friend’s QR code to send a connection request.</p><div id="qrReader"></div><div class="modal-actions"><button onclick="closeScanner()">Close</button></div></div></div>
<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    let people = [], conversations = [], activeConversationId = null, lastMessageId = 0, messageTimer = null, qrCode = null, qrScanner = null, profile = {}, currentProfileUserId = null;
    document.addEventListener('DOMContentLoaded', async () => { await loadProfile(); await loadPeople(); await loadConversations(); });
    async function loadProfile() { try { const r = await fetch('/api/profile', { headers: { Accept: 'application/json' } }); if (r.status === 401) return location.href = '/account'; const j = await r.json(); if (!j.success) return; profile = j.data; const u = profile; document.getElementById('profileName').textContent = u.name || 'Sohni member'; document.getElementById('profileAbout').textContent = u.about_me || 'Your community is waiting for you.'; document.getElementById('profileId').innerHTML = `<i class="fas fa-id-card"></i> ${escapeHtml(u.sohni_id || 'ID not set')}`; document.getElementById('profileLevel').textContent = u.sohni_id_type === 'premium' ? 'Premium' : 'Verified'; document.getElementById('friendCount').textContent = u.friends_count || 0; document.getElementById('followerCount').textContent = u.followers_count || 0; document.getElementById('groupCount').textContent = u.groups_count || 0; if (u.avatar_url) document.getElementById('bannerPhoto').innerHTML = `<img src="${escapeHtml(u.avatar_url)}" alt="Profile photo">`; } catch (e) { console.warn(e); } }
    async function loadPeople() { try { const r = await fetch('/api/friends/accepted', { headers: { Accept: 'application/json' } }); if (r.status === 401) return location.href = '/account'; const j = await r.json(); people = j.success ? (j.data || []) : []; renderPeople(); } catch (e) { document.getElementById('peopleGrid').innerHTML = '<div class="empty"><i class="fas fa-user-group"></i>Unable to load friends.</div>'; } }
    function renderPeople() { const term = (document.getElementById('peopleSearch').value || '').toLowerCase(); const level = document.getElementById('peopleFilter').value; const items = people.filter(p => (!term || `${p.name || ''} ${p.sohni_id || ''}`.toLowerCase().includes(term)) && (level === 'all' || (p.sohni_id_type || 'free') === level)); document.getElementById('peopleCount').textContent = `${items.length} friend${items.length === 1 ? '' : 's'}`; const grid = document.getElementById('peopleGrid'); if (!items.length) { grid.innerHTML = '<div class="empty"><i class="fas fa-user-group"></i>No accepted friends yet.<br>Friends appear here after they accept your connection.</div>'; return; } grid.innerHTML = items.map((p, i) => `<article class="user-card" style="animation-delay:${i * .04}s" onclick="openUserProfile(${Number(p.id)})">${p.unread ? `<span class="notification">${Number(p.unread)}</span>` : ''}<span class="verify"><i class="fas fa-circle-check"></i></span><div class="user-avatar">${p.avatar_url ? `<img src="${escapeHtml(p.avatar_url)}" alt="">` : '<i class="fas fa-user"></i>'}</div><h3>${escapeHtml(p.name || 'Sohni friend')}</h3><small>${escapeHtml(p.sohni_id || '14-digit ID')}</small><button class="follow" onclick="event.stopPropagation();startPersonChat(${Number(p.id)})"><i class="fas fa-comments"></i> Chat</button></article>`).join(''); }
    async function loadConversations() { try { const r = await fetch('/api/chat/conversations', { headers: { Accept: 'application/json' } }); if (r.status === 401) return location.href = '/account'; const j = await r.json(); conversations = j.success ? (j.data || []) : []; renderChatList(); } catch (e) { console.warn(e); } }
    // Bodies are end-to-end encrypted, so the preview cannot be decrypted here.
    function previewMessage(body) { if (!body) return 'No messages yet'; const looksEncrypted = !body.includes(' ') && body.length > 40 && /^[A-Za-z0-9+/=]+$/.test(body); return looksEncrypted ? '🔒 Encrypted message' : body; }
    function renderChatList() { const list = document.getElementById('chatList'); if (!conversations.length) { list.innerHTML = '<div class="empty"><i class="fas fa-comments"></i>No chats yet. Start chatting with a friend.</div>'; return; } list.innerHTML = conversations.map((c, i) => `<article class="user-card" style="animation-delay:${i * .04}s" onclick="goToChat(${c.id})"><div class="user-avatar">${c.avatar ? `<img src="${escapeHtml(c.avatar)}" alt="">` : '<i class="fas fa-user"></i>'}</div><h3>${escapeHtml(c.name || 'Chat')}</h3><small>${escapeHtml(previewMessage(c.last_message))}</small><button class="follow" onclick="event.stopPropagation();goToChat(${c.id})"><i class="fas fa-comments"></i> Open</button></article>`).join(''); }
    async function openChat(id) { activeConversationId = id; lastMessageId = 0; const c = conversations.find(x => x.id === id); document.getElementById('chatTitle').textContent = c?.name || 'Chat'; document.getElementById('chatMessages').innerHTML = ''; const chatRoom = document.querySelector('.chat-room'); if (chatRoom) { chatRoom.classList.add('active'); } renderChatList(); if (messageTimer) clearInterval(messageTimer); await fetchMessages(); messageTimer = setInterval(fetchMessages, 2000); }
    function goToChat(id) { window.location.href = `/chat?id=${id}`; }
    async function fetchMessages() { if (!activeConversationId) return; try { const r = await fetch(`/api/chat/messages/${activeConversationId}` + (lastMessageId ? `?after_id=${lastMessageId}` : ''), { headers: { Accept: 'application/json' } }); const j = await r.json(); if (!j.success || !j.data.length) return; const box = document.getElementById('chatMessages'); j.data.forEach(m => { lastMessageId = Math.max(lastMessageId, m.id); box.insertAdjacentHTML('beforeend', `<div class="bubble ${m.own ? 'own' : ''}">${escapeHtml(m.body)}<small style="display:block;margin-top:4px;opacity:.7;">${escapeHtml(m.time)}</small></div>`); }); box.scrollTop = box.scrollHeight; } catch (e) { console.warn(e); } }
    async function sendMessage(e) { e.preventDefault(); const input = document.getElementById('messageInput'); const body = input.value.trim(); if (!body || !activeConversationId) return; input.value = ''; const r = await fetch('/api/chat/messages', { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify({ conversation_id: activeConversationId, body }) }); const j = await r.json(); if (!j.success) { input.value = body; showToast(j.message || 'Message failed'); } else await fetchMessages(); }
    async function startPersonChat(id) { try { const r = await fetch('/api/chat/request', { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify({ user_id: id }) }); const j = await r.json(); if (!j.success) return showToast(j.message || 'This user does not accept communication requests.'); await loadConversations(); window.location.href = `/chat?id=${j.data.conversation_id}`; } catch (e) { showToast('Could not reach the server'); } }
    function openProfileQr() { document.getElementById('qrModal').classList.add('open'); const target = document.getElementById('profileQr'); target.innerHTML = ''; if (profile && profile.id && profile.sohni_id) { qrCode = new QRCode(target, { text: `${location.origin}/profile?user_id=${profile.id}&sohni_id=${encodeURIComponent(profile.sohni_id || '')}`, width: 220, height: 220, colorDark: '#0084ff', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.H }); } else { target.innerHTML = '<div class="empty"><i class="fas fa-exclamation"></i>Unable to generate QR code</div>'; } }
    function downloadProfileQr() { if (!profile || !profile.id) { showToast('Profile not loaded'); return; } if (!qrCode) { openProfileQr(); } setTimeout(() => { const canvas = document.querySelector('#profileQr canvas'); if (!canvas) { showToast('QR code not ready'); return; } const link = document.createElement('a'); link.download = `sohni-${profile.sohni_id || 'profile'}-qr.png`; link.href = canvas.toDataURL('image/png'); link.click(); showToast('QR code downloaded'); }, 100); }
    function closeQrModal() { document.getElementById('qrModal').classList.remove('open'); }
    function ensureQrLibrary() {
        if (window.Html5Qrcode) return Promise.resolve();
        if (window.__qrLibPromise) return window.__qrLibPromise;
        const sources = [
            'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js',
            'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js'
        ];
        window.__qrLibPromise = new Promise((resolve, reject) => {
            let index = 0;
            const tryNext = () => {
                if (index >= sources.length) return reject(new Error('Unable to load QR library'));
                const script = document.createElement('script');
                script.src = sources[index++];
                script.onload = () => window.Html5Qrcode ? resolve() : tryNext();
                script.onerror = tryNext;
                document.head.appendChild(script);
            };
            tryNext();
        });
        return window.__qrLibPromise;
    }

    async function openScanner() {
        document.getElementById('scannerModal').classList.add('open');
        if (qrScanner) return;

        const reader = document.getElementById('qrReader');
        reader.innerHTML = '<div class="empty" style="padding:30px;"><i class="fas fa-spinner fa-spin"></i>Starting camera…</div>';

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            reader.innerHTML = '<div class="empty" style="padding:30px;"><i class="fas fa-camera"></i>Camera needs HTTPS or localhost.</div>';
            return showToast('Camera requires a secure (https) connection');
        }

        try {
            await ensureQrLibrary();
        } catch (e) {
            reader.innerHTML = '<div class="empty" style="padding:30px;"><i class="fas fa-triangle-exclamation"></i>QR library failed to load.</div>';
            return showToast('QR scanner library not loaded');
        }

        try {
            reader.innerHTML = '';
            qrScanner = new Html5Qrcode('qrReader');
            await qrScanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                text => handleQrScan(text),
                () => {}
            );
        } catch (e) {
            console.error('Scanner error:', e);
            qrScanner = null;
            reader.innerHTML = '<div class="empty" style="padding:30px;"><i class="fas fa-camera"></i>Camera access denied or unavailable.</div>';
            showToast(e?.message ? `Camera error: ${e.message}` : 'Could not start the camera');
        }
    }

    async function closeScanner() {
        document.getElementById('scannerModal').classList.remove('open');
        if (!qrScanner) return;
        try { await qrScanner.stop(); qrScanner.clear(); } catch (e) { console.warn('Scanner stop error:', e); }
        qrScanner = null;
        document.getElementById('qrReader').innerHTML = '';
    }

    function handleQrScan(text) {
        let userId = null;
        try { userId = new URL(text).searchParams.get('user_id'); } catch (e) { userId = null; }
        if (!userId) return showToast('Invalid Sohni QR code');
        closeScanner();
        startPersonChat(Number(userId));
    }
    function openUserProfile(id) { const p = people.find(x => x.id === id); if (!p) return; currentProfileUserId = id; document.getElementById('userProfileCard').innerHTML = `<div class="profile-card"><div class="profile-card-photo">${p.avatar_url ? `<img src="${escapeHtml(p.avatar_url)}" alt="">` : '<i class="fas fa-user"></i>'}</div><div><h2>${escapeHtml(p.name || 'Sohni user')} <span style="color:var(--green);font-size:13px;"><i class="fas fa-circle-check"></i></span></h2><p>${escapeHtml(p.sohni_id || '')}<br>This user accepts communication requests.</p></div><div class="card-actions"><button onclick="startPersonChat(${Number(p.id)})">Chat</button><button class="block" onclick="blockUser(${Number(p.id)})">Block</button><button class="block" onclick="reportUser()">Report</button></div></div>`; showPanel('user', null); }
    async function blockUser(id) { try { const r = await fetch('/api/settings/blocked-users/add', { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify({ user_id: id }) }); const j = await r.json(); showToast(j.message || 'User blocked'); if (j.success) { people = people.filter(p => p.id !== id); showPanel('friends', document.querySelector('.panel-tab')); renderPeople(); } } catch (e) { showToast('Could not block user'); } }
    async function reportUser() {
        if (!currentProfileUserId) return showToast('No user selected');
        const reason = prompt('Why are you reporting this user? (spam, harassment, abuse, other)', 'other');
        if (reason === null) return;
        try {
            const r = await fetch('/api/reports', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ reported_user_id: currentProfileUserId, reason })
            });
            const j = await r.json();
            showToast(j.success ? 'Report submitted for review' : (j.message || 'Could not submit report'));
        } catch (e) {
            showToast('Could not reach the server');
        }
    }
    function showPanel(name, button) { document.querySelectorAll('.sub-panel').forEach(p => p.classList.remove('active')); document.getElementById('panel-' + name).classList.add('active'); document.querySelectorAll('.panel-tab').forEach(b => b.classList.remove('active')); if (button) button.classList.add('active'); }
    function openRequestModal() { document.getElementById('requestModal').classList.add('open'); document.getElementById('requestIdentifier').focus(); }
    function closeRequestModal() { document.getElementById('requestModal').classList.remove('open'); }
    async function sendRequest() { const identifier = document.getElementById('requestIdentifier').value.trim(); if (!/^\d{14}$/.test(identifier)) return showToast('Enter a valid 14-digit Sohni ID'); try { const r = await fetch('/api/chat/start', { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify({ identifier }) }); const j = await r.json(); if (!j.success) return showToast(j.message || 'This user does not accept communication requests.'); closeRequestModal(); await loadConversations(); window.location.href = `/chat?id=${j.data.conversation_id}`; } catch (e) { showToast('Could not reach the server'); } }
    function filterCards(input, id) { const q = input.value.toLowerCase(); document.querySelectorAll(`#${id} .user-card`).forEach(card => card.style.display = card.innerText.toLowerCase().includes(q) ? '' : 'none'); }
    function showToast(message) { const t = document.getElementById('toast'); t.textContent = message; t.classList.add('show'); clearTimeout(window.toastTimer); window.toastTimer = setTimeout(() => t.classList.remove('show'), 2500); }
    async function logout() { await fetch('/api/auth/logout', { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': CSRF } }); location.href = '/account'; }
    function escapeHtml(value) { return String(value ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c])); }
</script>
</body>
</html>
