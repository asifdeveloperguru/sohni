<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Call — Sohni</title>
    <link rel="icon" href="/images/app_logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --blue: #2563eb; --blue-dark: #1d4ed8; --danger: #ef4444; --danger-dark: #dc2626;
            --green: #10b981; --amber: #f59e0b;
            --stage: #0b1220; --panel: #151d2e; --panel-2: #1e2739;
            --text: #f1f5f9; --muted: #94a3b8; --line: #2a3446;
            --r-sm: 10px; --r-md: 14px; --r-lg: 20px;
        }
        * { box-sizing: border-box; }
        body { margin: 0; height: 100vh; overflow: hidden; background: var(--stage); color: var(--text); font-family: Inter, system-ui, sans-serif; -webkit-font-smoothing: antialiased; }
        button { font: inherit; cursor: pointer; }

        .call-shell { display: flex; flex-direction: column; height: 100vh; }

        .call-top { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 14px 22px; border-bottom: 1px solid var(--line); }
        .call-meta { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .call-meta h1 { margin: 0; font: 600 15px Sora, sans-serif; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .call-meta span { color: var(--muted); font-size: 12.5px; font-variant-numeric: tabular-nums; }
        .badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 11px; border-radius: 999px; background: rgba(16,185,129,.14); color: #6ee7b7; font-size: 11.5px; font-weight: 500; }
        .badge.warn { background: rgba(245,158,11,.14); color: #fcd34d; }

        .stage { flex: 1; display: grid; gap: 12px; padding: 16px 22px; overflow: auto; align-content: center;
                 grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }
        .stage.solo { grid-template-columns: minmax(0, 1fr); }

        .tile { position: relative; aspect-ratio: 16/9; border-radius: var(--r-lg); overflow: hidden; background: var(--panel); border: 1px solid var(--line); }
        .tile video { width: 100%; height: 100%; object-fit: cover; background: #000; display: block; }
        .tile.speaking { border-color: var(--green); box-shadow: 0 0 0 3px rgba(16,185,129,.25); }
        .tile-off { position: absolute; inset: 0; display: grid; place-items: center; gap: 10px; background: var(--panel); }
        .tile-avatar { width: 76px; height: 76px; display: grid; place-items: center; border-radius: 50%; background: linear-gradient(135deg, var(--blue), #06b6d4); font: 600 28px Sora, sans-serif; }
        .tile-bar { position: absolute; left: 12px; right: 12px; bottom: 12px; display: flex; align-items: center; gap: 8px; }
        .tile-name { padding: 5px 11px; border-radius: 999px; background: rgba(11,18,32,.72); backdrop-filter: blur(6px); font-size: 12.5px; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .tile-icon { width: 26px; height: 26px; display: grid; place-items: center; border-radius: 50%; background: rgba(11,18,32,.72); font-size: 11px; }
        .tile-icon.muted { background: rgba(239,68,68,.85); }
        .quality { margin-left: auto; display: flex; align-items: flex-end; gap: 2px; height: 14px; padding: 5px 8px; border-radius: 999px; background: rgba(11,18,32,.72); }
        .quality i { width: 3px; border-radius: 2px; background: var(--muted); }
        .quality i:nth-child(1) { height: 5px; } .quality i:nth-child(2) { height: 9px; } .quality i:nth-child(3) { height: 13px; }
        .quality.good i { background: var(--green); }
        .quality.fair i:nth-child(1), .quality.fair i:nth-child(2) { background: var(--amber); }
        .quality.poor i:nth-child(1) { background: var(--danger); }

        .controls { display: flex; align-items: center; justify-content: center; gap: 12px; padding: 18px 22px 24px; border-top: 1px solid var(--line); }
        .ctrl { width: 54px; height: 54px; display: grid; place-items: center; border: 0; border-radius: 50%; background: var(--panel-2); color: var(--text); font-size: 18px; transition: background .15s, transform .15s; }
        .ctrl:hover { background: #2a3446; transform: translateY(-2px); }
        .ctrl.active { background: #fff; color: var(--stage); }
        .ctrl.hangup { width: 66px; background: var(--danger); }
        .ctrl.hangup:hover { background: var(--danger-dark); }

        .overlay { position: fixed; inset: 0; z-index: 30; display: none; place-items: center; padding: 24px; background: rgba(4,8,16,.86); backdrop-filter: blur(8px); }
        .overlay.open { display: grid; }
        .overlay-box { width: min(400px, 100%); padding: 30px; border: 1px solid var(--line); border-radius: var(--r-lg); background: var(--panel); text-align: center; }
        .overlay-box .tile-avatar { margin: 0 auto 16px; }
        .overlay-box h2 { margin: 0 0 6px; font: 600 20px Sora, sans-serif; }
        .overlay-box p { margin: 0 0 22px; color: var(--muted); font-size: 14px; line-height: 1.6; }
        .overlay-actions { display: flex; gap: 12px; justify-content: center; }
        .round-btn { width: 58px; height: 58px; display: grid; place-items: center; border: 0; border-radius: 50%; color: #fff; font-size: 20px; }
        .round-btn.accept { background: var(--green); }
        .round-btn.reject { background: var(--danger); }

        .toast { position: fixed; left: 50%; bottom: 100px; z-index: 40; padding: 11px 18px; border-radius: var(--r-sm); background: var(--panel-2); border: 1px solid var(--line); opacity: 0; transform: translate(-50%,10px); transition: .22s; pointer-events: none; font-size: 13.5px; }
        .toast.show { opacity: 1; transform: translate(-50%,0); }

        @media (max-width: 640px) {
            .stage { padding: 12px; grid-template-columns: 1fr; }
            .controls { gap: 9px; padding: 14px 12px 20px; }
            .ctrl { width: 48px; height: 48px; font-size: 16px; }
            .ctrl.hangup { width: 58px; }
            .call-top { padding: 12px 14px; }
        }
    </style>
</head>
<body>
<div class="call-shell">
    <header class="call-top">
        <div class="call-meta">
            <h1 id="callTitle">Connecting…</h1>
            <span id="callTimer">00:00</span>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
            <span class="badge"><i class="fas fa-lock"></i> End-to-end encrypted</span>
            <span class="badge warn" id="turnWarning" style="display:none"><i class="fas fa-triangle-exclamation"></i> No TURN relay</span>
        </div>
    </header>

    <main class="stage solo" id="stage"></main>

    <footer class="controls">
        <button class="ctrl" id="micBtn" onclick="toggleMic()" title="Mute"><i class="fas fa-microphone"></i></button>
        <button class="ctrl" id="camBtn" onclick="toggleCam()" title="Camera"><i class="fas fa-video"></i></button>
        <button class="ctrl" id="screenBtn" onclick="toggleScreen()" title="Share screen"><i class="fas fa-display"></i></button>
        <button class="ctrl hangup" onclick="hangUp()" title="Leave call"><i class="fas fa-phone-slash"></i></button>
    </footer>
</div>

<div class="overlay" id="incomingOverlay">
    <div class="overlay-box">
        <div class="tile-avatar" id="incomingAvatar">?</div>
        <h2 id="incomingName">Incoming call</h2>
        <p id="incomingMode">Video call</p>
        <div class="overlay-actions">
            <button class="round-btn reject" onclick="declineCall()"><i class="fas fa-phone-slash"></i></button>
            <button class="round-btn accept" onclick="acceptCall()"><i class="fas fa-phone"></i></button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

@include('partials.echo')
<script src="/js/sohni-call.js"></script>
<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const params = new URLSearchParams(location.search);
    const roomId = params.get('room');
    const autoJoin = params.get('join') === '1';

    let call = null, room = null, timerHandle = null, startedAt = null;
    let micMuted = false, camOff = false, sharing = false;
    const tiles = new Map();

    document.addEventListener('DOMContentLoaded', async () => {
        if (!roomId) return location.href = '/dashboard';

        try {
            const res = await fetch(`/api/calls/${roomId}`, { headers: { Accept: 'application/json' } });
            const json = await res.json();
            if (!json.success) { showToast('Call unavailable'); return backToDashboard(); }
            room = json.data;
        } catch (e) {
            return backToDashboard();
        }

        document.getElementById('callTitle').textContent = describeRoom();
        if (room.mode === 'audio') document.getElementById('camBtn').style.display = 'none';

        const mine = room.participants.find(p => p.id === room.me);
        if (!autoJoin && mine && mine.state === 'invited') showIncoming();
        else joinCall();
    });

    function describeRoom() {
        const others = room.participants.filter(p => p.id !== room.me).map(p => p.name);
        if (!others.length) return 'Call';
        return others.length === 1 ? others[0] : `${others[0]} +${others.length - 1}`;
    }

    function showIncoming() {
        const host = room.participants.find(p => p.id === room.host_id);
        document.getElementById('incomingName').textContent = host ? host.name : 'Incoming call';
        document.getElementById('incomingAvatar').textContent = (host?.name || '?').charAt(0).toUpperCase();
        document.getElementById('incomingMode').textContent = room.mode === 'video' ? 'Video call' : 'Voice call';
        document.getElementById('incomingOverlay').classList.add('open');
    }

    function acceptCall() {
        document.getElementById('incomingOverlay').classList.remove('open');
        joinCall();
    }

    async function declineCall() {
        await fetch(`/api/calls/${roomId}/decline`, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': CSRF } });
        backToDashboard();
    }

    async function joinCall() {
        try {
            const res = await fetch(`/api/calls/${roomId}/join`, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': CSRF }
            });
            const json = await res.json();
            if (!json.success) { showToast(json.message || 'Could not join'); return backToDashboard(); }
            room = json.data;
        } catch (e) {
            return backToDashboard();
        }

        call = new SohniCall({
            csrf: CSRF,
            echo: window.Echo,
            onLocalStream: stream => addTile(room.me, 'You', stream, true),
            onRemoteStream: (peerId, stream) => addTile(peerId, nameOf(peerId), stream, false),
            onPeerLeft: peerId => removeTile(peerId),
            onStateChange: handleState,
            onStats: (peerId, stats) => updateQuality(peerId, stats),
            onError: err => console.warn('Call error:', err)
        });

        try {
            const ice = await call.loadIceServers();
            if (!ice.has_turn) document.getElementById('turnWarning').style.display = '';

            await call.start({ roomId, myId: room.me, mode: room.mode });
            startTimer();
        } catch (e) {
            showToast(e.name === 'NotAllowedError' ? 'Camera/microphone permission denied' : 'Could not start media');
            console.warn(e);
        }
    }

    function nameOf(peerId) {
        return room.participants.find(p => p.id === peerId)?.name || 'Participant';
    }

    function addTile(peerId, name, stream, isLocal) {
        let tile = tiles.get(peerId);

        if (!tile) {
            const el = document.createElement('div');
            el.className = 'tile';
            el.innerHTML = `
                <video autoplay playsinline ${isLocal ? 'muted' : ''}></video>
                <div class="tile-off" style="display:none">
                    <div class="tile-avatar">${(name || '?').charAt(0).toUpperCase()}</div>
                </div>
                <div class="tile-bar">
                    <span class="tile-name">${escapeHtml(name)}${isLocal ? ' (you)' : ''}</span>
                    <span class="tile-icon muted" style="display:none"><i class="fas fa-microphone-slash"></i></span>
                    <span class="quality good"><i></i><i></i><i></i></span>
                </div>`;
            document.getElementById('stage').appendChild(el);
            tile = { el, video: el.querySelector('video') };
            tiles.set(peerId, tile);
        }

        tile.video.srcObject = stream;
        refreshLayout();
    }

    function removeTile(peerId) {
        const tile = tiles.get(peerId);
        if (!tile) return;
        tile.el.remove();
        tiles.delete(peerId);
        refreshLayout();
    }

    function refreshLayout() {
        document.getElementById('stage').classList.toggle('solo', tiles.size <= 1);
    }

    function updateQuality(peerId, stats) {
        const tile = tiles.get(peerId);
        if (!tile) return;
        const bars = tile.el.querySelector('.quality');
        bars.className = `quality ${stats.quality}`;
        bars.title = `${stats.rtt} ms · ${stats.loss}% loss · ${stats.kbps} kbps`;
    }

    function handleState(action, data) {
        if (action === 'ended') { showToast('Call ended'); setTimeout(backToDashboard, 1200); }
        if (action === 'declined') showToast(`${data?.user?.name || 'They'} declined`);
        if (action === 'left') removeTile(data?.user?.id);
        if (action === 'peer-joined') showToast(`${data?.name || 'Someone'} joined`);
    }

    function toggleMic() {
        micMuted = !micMuted;
        call?.setMuted(micMuted);
        const btn = document.getElementById('micBtn');
        btn.classList.toggle('active', micMuted);
        btn.innerHTML = `<i class="fas fa-microphone${micMuted ? '-slash' : ''}"></i>`;
        tiles.get(room.me)?.el.querySelector('.tile-icon').style.setProperty('display', micMuted ? 'grid' : 'none');
    }

    function toggleCam() {
        camOff = !camOff;
        call?.setCameraOff(camOff);
        const btn = document.getElementById('camBtn');
        btn.classList.toggle('active', camOff);
        btn.innerHTML = `<i class="fas fa-video${camOff ? '-slash' : ''}"></i>`;
        const tile = tiles.get(room.me);
        if (tile) tile.el.querySelector('.tile-off').style.display = camOff ? 'grid' : 'none';
    }

    async function toggleScreen() {
        try {
            if (sharing) { await call.stopScreenShare(); sharing = false; }
            else { await call.shareScreen(); sharing = true; }
            document.getElementById('screenBtn').classList.toggle('active', sharing);
        } catch (e) {
            showToast('Screen sharing cancelled');
        }
    }

    async function hangUp() {
        await call?.hangUp();
        backToDashboard();
    }

    function startTimer() {
        startedAt = Date.now();
        timerHandle = setInterval(() => {
            const s = Math.floor((Date.now() - startedAt) / 1000);
            const label = `${String(Math.floor(s / 60)).padStart(2, '0')}:${String(s % 60).padStart(2, '0')}`;
            document.getElementById('callTimer').textContent = label;
        }, 1000);
    }

    function backToDashboard() {
        clearInterval(timerHandle);
        location.href = room?.conversation_id ? `/chat?id=${room.conversation_id}` : '/dashboard';
    }

    window.addEventListener('beforeunload', () => { call?.hangUp(); });

    function showToast(message) {
        const t = document.getElementById('toast');
        t.textContent = message;
        t.classList.add('show');
        clearTimeout(window.toastTimer);
        window.toastTimer = setTimeout(() => t.classList.remove('show'), 2600);
    }

    function escapeHtml(v) {
        return String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c]));
    }
</script>
</body>
</html>
