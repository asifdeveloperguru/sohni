<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Profile — Sohni</title>
    <link rel="icon" type="image/png" href="/images/app_logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode@1.7.15/minified/html5-qrcode.min.js"></script>
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

        /* ---------- Loader ---------- */
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

        /* ---------- Top Bar ---------- */
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

        /* ---------- Shell ---------- */
        .shell {
            max-width: 100%;
            width: 100%;
            margin: 0 auto;
            padding: 26px 24px 60px;
        }

        /* ---------- Hero ---------- */
        .hero {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--r-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            margin-bottom: 24px;
            width: 100%;
        }

        .cover {
            position: relative;
            height: 260px;
            width: 100%;
            background:
                radial-gradient(1000px 340px at 12% -30%, rgba(255, 255, 255, .45), transparent 60%),
                linear-gradient(120deg, var(--neon) 0%, var(--neon-2) 100%);
            background-size: cover;
            background-position: center;
            cursor: pointer;
            overflow: hidden;
        }
        .cover::after {
            content: ''; position: absolute; inset: 0;
            background:
                radial-gradient(420px 220px at 88% 20%, rgba(255, 255, 255, .18), transparent 70%),
                radial-gradient(320px 200px at 20% 90%, rgba(255, 255, 255, .12), transparent 70%);
            pointer-events: none;
        }
        .cover-veil {
            position: absolute; inset: 0; z-index: 2;
            display: grid; place-content: center;
            background: rgba(15, 23, 42, 0);
            opacity: 0; transition: all .28s ease;
        }
        .cover:hover .cover-veil { opacity: 1; background: rgba(15, 23, 42, .34); }
        .cover-edit {
            width: 54px; height: 54px; border-radius: 50%;
            display: grid; place-content: center;
            background: #fff; color: var(--neon);
            font-size: 20px; border: none; cursor: pointer;
            box-shadow: 0 10px 26px rgba(15, 23, 42, .26);
            transition: transform .25s cubic-bezier(.34, 1.5, .64, 1);
        }
        .cover-edit:hover { transform: scale(1.1); }

        .hero-body {
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: flex-start; gap: 24px;
            padding: 0 32px 30px;
            margin-top: -68px;
            position: relative; z-index: 3;
        }

        .avatar {
            width: 168px; height: 168px; border-radius: 50%;
            border: 6px solid #fff;
            background: linear-gradient(135deg, var(--neon), var(--neon-2));
            display: grid; place-content: center;
            font-size: 66px; color: #fff;
            overflow: hidden; flex-shrink: 0;
            box-shadow: 0 14px 34px rgba(0, 132, 255, .26);
        }
        .avatar img { width: 100%; height: 100%; object-fit: cover; }

        .identity { padding-top: 76px; min-width: 0; }
        .identity h1 {
            font-size: 27px; font-weight: 700; color: var(--ink);
            letter-spacing: -.4px; line-height: 1.2;
            overflow-wrap: anywhere;
        }
        .identity .handle {
            margin-top: 5px; font-size: 14px; color: var(--muted); font-weight: 500;
        }
        .identity-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-top: 14px; }

        .chip {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 13px; border-radius: 999px;
            font-size: 11px; font-weight: 700;
            letter-spacing: .6px; text-transform: uppercase;
            font-family: 'Sora', sans-serif;
        }
        .chip.free { background: var(--neon-soft); color: var(--neon); border: 1px solid #cfe8ff; }
        .chip.premium { background: linear-gradient(120deg, #fff6da, #fff0c2); color: #b8860b; border: 1px solid #ffe08a; box-shadow: 0 3px 10px rgba(255, 193, 7, .2); }
        .chip.soft { background: #f5f7fa; color: var(--muted); border: 1px solid var(--line); text-transform: none; letter-spacing: .2px; font-weight: 600; }
        .chip.ok { background: #edfaf1; color: var(--success); border: 1px solid #c8ecd4; }

        .hero-actions { display: flex; flex-direction: column; gap: 10px; padding-top: 76px; }
        .hero-actions .btn { min-width: 152px; }

        /* ---------- Layout ---------- */
        .grid {
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr) 360px 280px;
            gap: 24px;
            align-items: start;
            margin: 0 auto;
            float: none;
            width: 100%;
        }

        /* ---------- Friends & Followers Layout ---------- */
        .social-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 28px;
            margin-top: 32px;
            width: 100%;
        }

        .social-section-left {
            order: -1;
            grid-column: 1;
            grid-row: 1;
        }

        .social-section-right {
            order: 1;
            grid-column: 4;
            grid-row: 1;
        }

        .panel {
            order: 0;
            grid-column: 2;
            grid-row: 1;
        }

        .side {
            order: 0;
            grid-column: 3;
            grid-row: 1;
        }
        .panel {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--r-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        .tabs {
            display: flex; gap: 6px;
            padding: 10px 12px;
            border-bottom: 1px solid var(--line);
            background: linear-gradient(180deg, var(--tint), #fff);
            overflow-x: auto;
        }
        .tab {
            display: inline-flex; align-items: center; gap: 8px;
            font-family: 'Sora', sans-serif;
            font-size: 13px; font-weight: 600;
            color: var(--muted);
            padding: 10px 18px; border-radius: 999px;
            border: 1px solid transparent; background: transparent;
            cursor: pointer; white-space: nowrap;
            transition: all .2s ease;
        }
        .tab:hover { color: var(--neon); background: var(--neon-soft); }
        .tab.active {
            color: #fff;
            background: linear-gradient(120deg, var(--neon), var(--neon-2));
            box-shadow: var(--shadow-neon);
        }

        .panel-body { padding: 26px 28px 30px; }
        .tabpane { display: none; animation: rise .32s ease; }
        .tabpane.active { display: block; }
        @keyframes rise { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }

        .pane-head {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 20px; padding-bottom: 16px;
            border-bottom: 1px solid var(--line);
        }
        .pane-head .ico {
            width: 40px; height: 40px; border-radius: 12px;
            display: grid; place-content: center;
            background: linear-gradient(135deg, var(--neon), var(--neon-2));
            color: #fff; font-size: 15px;
            box-shadow: var(--shadow-neon);
        }
        .pane-head h2 { font-size: 17px; font-weight: 700; color: var(--ink); }
        .pane-head .sub { font-size: 12px; color: var(--muted); margin-top: 2px; font-family: 'Inter', sans-serif; font-weight: 500; }

        /* ---------- Info rows ---------- */
        .rows { display: flex; flex-direction: column; gap: 10px; }
        .row {
            position: relative; overflow: hidden;
            display: flex; align-items: center; justify-content: space-between; gap: 18px;
            padding: 15px 18px;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--r-md);
            box-shadow: var(--shadow-sm);
            transition: all .24s ease;
        }
        .row::before {
            content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px;
            background: linear-gradient(180deg, var(--neon), var(--neon-2));
            transform: scaleY(0); transform-origin: center;
            transition: transform .24s ease;
        }
        .row:hover {
            border-color: #d8e9fb;
            box-shadow: 0 8px 22px rgba(0, 132, 255, .1);
            transform: translateX(3px);
        }
        .row:hover::before { transform: scaleY(1); }
        .row .k {
            display: flex; align-items: center; gap: 9px;
            font-size: 11px; font-weight: 700; letter-spacing: 1px;
            text-transform: uppercase; color: var(--muted);
            font-family: 'Sora', sans-serif; flex-shrink: 0;
        }
        .row .k i { color: var(--neon); font-size: 13px; }
        .row .v { font-size: 14.5px; font-weight: 600; color: var(--ink); text-align: right; overflow-wrap: anywhere; }
        .row .v.ok { color: var(--success); }

        .about-copy {
            margin-top: 16px;
            padding: 16px 18px;
            color: var(--text);
            background: var(--tint);
            border: 1px solid var(--line);
            border-left: 3px solid var(--neon);
            border-radius: var(--r-md);
            line-height: 1.7;
            white-space: pre-line;
        }
        .experience-list { display: flex; flex-direction: column; gap: 12px; }
        .experience-item {
            padding: 16px 18px;
            border: 1px solid var(--line);
            border-left: 3px solid var(--neon-2);
            border-radius: var(--r-md);
            background: var(--surface);
            box-shadow: var(--shadow-sm);
        }
        .experience-item h3 { color: var(--ink); font-size: 15px; margin-bottom: 4px; }
        .experience-meta { color: var(--neon); font-size: 12px; font-weight: 700; margin-bottom: 7px; }
        .experience-item p { color: var(--muted); font-size: 12px; line-height: 1.6; white-space: pre-line; }

        /* ---------- Education ---------- */
        .edu {
            position: relative;
            padding: 18px 20px 18px 22px;
            background: var(--surface);
            border: 1px solid var(--line);
            border-left: 3px solid var(--neon);
            border-radius: var(--r-md);
            box-shadow: var(--shadow-sm);
            margin-bottom: 12px;
            transition: all .24s ease;
        }
        .edu:hover {
            transform: translateX(4px);
            box-shadow: 0 10px 26px rgba(0, 132, 255, .12);
            border-left-color: var(--neon-2);
        }
        .edu-title {
            display: flex; align-items: center; gap: 9px;
            font-size: 15px; font-weight: 700; color: var(--ink); margin-bottom: 10px;
        }
        .edu-title i { color: var(--neon); }
        .edu-meta { display: flex; flex-wrap: wrap; gap: 8px; }
        .edu-meta span {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 12px; font-weight: 600; color: var(--muted);
            background: var(--tint); border: 1px solid var(--line);
            padding: 5px 11px; border-radius: 999px;
        }
        .edu-meta i { color: var(--neon); font-size: 11px; }

        .empty {
            text-align: center; padding: 54px 20px;
            border: 1.5px dashed #d8e9fb; border-radius: var(--r-md);
            background: var(--tint); color: var(--muted);
        }
        .empty i { font-size: 34px; color: #b9d9f7; margin-bottom: 12px; display: block; }
        .empty p { font-size: 14px; font-weight: 500; }

        /* ---------- Sidebar ---------- */
        .side { display: flex; flex-direction: column; gap: 20px; }
        .card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--r-lg);
            box-shadow: var(--shadow-md);
            padding: 22px;
            transition: box-shadow .25s ease;
        }
        .card:hover { box-shadow: 0 6px 16px rgba(15, 23, 42, .06), 0 18px 40px rgba(15, 23, 42, .08); }
        .card-head {
            display: flex; align-items: center; gap: 9px;
            font-family: 'Sora', sans-serif;
            font-size: 12px; font-weight: 700;
            letter-spacing: 1.1px; text-transform: uppercase;
            color: var(--muted); margin-bottom: 18px;
        }
        .card-head i { color: var(--neon); font-size: 14px; }

        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .stat {
            text-align: center; padding: 14px 8px;
            background: var(--tint); border: 1px solid var(--line);
            border-radius: var(--r-sm);
            transition: all .22s ease;
        }
        .stat:hover { border-color: #cfe8ff; transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0, 132, 255, .1); }
        .stat b {
            display: block; font-family: 'Space Grotesk', sans-serif;
            font-size: 20px; font-weight: 700;
            background: linear-gradient(120deg, var(--neon), var(--neon-2));
            -webkit-background-clip: text; background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .stat small {
            display: block; margin-top: 3px;
            font-size: 10px; font-weight: 700; letter-spacing: .8px;
            text-transform: uppercase; color: var(--muted);
        }

        .id-box {
            text-align: center; padding: 18px 14px;
            border: 1.5px dashed #b9d9f7; border-radius: var(--r-md);
            background: linear-gradient(135deg, var(--neon-soft), #f4fbff);
            transition: all .24s ease;
        }
        .id-box:hover { border-style: solid; border-color: var(--neon); box-shadow: 0 6px 18px rgba(0, 132, 255, .14); }
        .id-box small {
            display: block; font-size: 10px; font-weight: 700;
            letter-spacing: 1.2px; text-transform: uppercase; color: var(--muted); margin-bottom: 8px;
        }
        .id-box b {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 17px; font-weight: 700; color: var(--neon);
            letter-spacing: .6px; overflow-wrap: anywhere;
        }
        .copy-btn {
            margin-top: 12px; width: 100%;
            font-size: 12px; padding: 9px 14px;
        }

        .qr-frame {
            position: relative;
            display: grid; place-items: center;
            padding: 20px; margin-bottom: 14px;
            border: 1.5px solid #d8e9fb; border-radius: var(--r-md);
            background: linear-gradient(160deg, #fff, var(--tint));
            box-shadow: inset 0 0 0 5px #fff, 0 8px 22px rgba(0, 132, 255, .1);
        }
        .qr-frame canvas, .qr-frame img {
            display: block; max-width: 100%; height: auto;
            border-radius: 8px;
        }
        .qr-hint { font-size: 11.5px; color: var(--muted); text-align: center; margin-bottom: 14px; line-height: 1.5; }
        .qr-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .qr-actions .btn { min-width: 0; font-size: 12px; padding: 9px 10px; }

        /* ---------- Modal ---------- */
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
        #reader { width: 100%; border-radius: var(--r-md); overflow: hidden; }

        /* ---------- Friends & Followers Section ---------- */
        .social-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 28px;
            margin-top: 32px;
            width: 100%;
        }
        .social-column {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--r-lg);
            padding: 26px;
            box-shadow: var(--shadow-md);
            display: flex;
            flex-direction: column;
        }
        .social-column-head {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 22px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--line);
            font-family: 'Sora', sans-serif;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.1px;
            text-transform: uppercase;
            color: var(--muted);
        }
        .social-column-head i {
            color: var(--neon);
            font-size: 14px;
        }

        .frames-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }
        
        .frame {
            position: relative; aspect-ratio: 1;
            border-radius: 12px; overflow: hidden;
            border: 2px solid var(--line);
            background: linear-gradient(135deg, var(--neon-soft), #f4fbff);
            cursor: pointer; group; transition: all .28s cubic-bezier(.34, 1.4, .64, 1);
        }
        .frame:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0, 132, 255, .18); }

        .frame-bg {
            position: absolute; inset: 0;
            background-size: cover; background-position: center;
            background-color: linear-gradient(135deg, var(--neon), var(--neon-2));
        }

        .frame-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(180deg, transparent 40%, rgba(15, 23, 42, .8) 100%);
            display: flex; flex-direction: column; justify-content: flex-end;
            padding: 12px;
        }

        .frame-name { font-size: 13px; font-weight: 700; color: #fff; margin-bottom: 6px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        
        .frame-level {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 9px; font-weight: 700; padding: 3px 8px;
            border-radius: 999px; text-transform: uppercase; width: fit-content;
            position: absolute; bottom: 8px; right: 8px;
        }

        .frame.free .frame-level {
            background: #e8f4ff; color: var(--neon); border: 1px solid #cfe8ff;
        }

        .frame.pro .frame-level {
            background: linear-gradient(120deg, #fff6da, #fff0c2);
            color: #b8860b; border: 1px solid #ffe08a;
            box-shadow: 0 2px 8px rgba(255, 193, 7, .2);
        }

        .frame.thunder .frame-level {
            background: linear-gradient(120deg, #ff0080, #ff8c00);
            color: #fff; border: 1px solid #ff1493;
            box-shadow: 0 0 12px rgba(255, 0, 128, .6);
            animation: thunder-pulse 2s ease-in-out infinite;
        }

        @keyframes thunder-pulse {
            0%, 100% { box-shadow: 0 0 12px rgba(255, 0, 128, .6), inset 0 0 0 0 rgba(255, 0, 128, 0); }
            50% { box-shadow: 0 0 20px rgba(255, 0, 128, .9), inset 0 0 8px 2px rgba(255, 0, 128, .3); }
        }

        .frame.thunder {
            border-color: #ff1493;
            box-shadow: 0 0 20px rgba(255, 0, 128, .4);
            animation: thunder-frame-glow 3s ease-in-out infinite;
        }

        .frame.thunder:hover {
            box-shadow: 0 0 32px rgba(255, 0, 128, .6), 0 12px 32px rgba(255, 0, 128, .25);
        }

        @keyframes thunder-frame-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(255, 0, 128, .4); }
            50% { box-shadow: 0 0 32px rgba(255, 0, 128, .7), inset 0 0 20px rgba(255, 0, 128, .1); }
        }

        .frame.thunder .frame-overlay::after {
            content: ''; position: absolute; inset: 0;
            background: repeating-linear-gradient(
                90deg,
                rgba(255, 255, 255, 0) 0px,
                rgba(255, 255, 255, .05) 1px,
                rgba(255, 255, 255, 0) 2px,
                rgba(255, 255, 255, 0) 4px
            );
            opacity: 0;
            animation: thunder-flicker .15s infinite;
            pointer-events: none;
        }

        @keyframes thunder-flicker {
            0%, 89%, 100% { opacity: 0; }
            90%, 95% { opacity: 1; }
        }

        .empty-state {
            grid-column: 1 / -1; text-align: center; padding: 40px 20px;
            border: 1.5px dashed #d8e9fb; border-radius: var(--r-md);
            background: var(--tint); color: var(--muted);
        }
        .empty-state i { font-size: 36px; color: #b9d9f7; margin-bottom: 12px; display: block; }
        .empty-state p { font-size: 14px; font-weight: 500; }

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
        .toast i { color: var(--neon-2); }

        input[type="file"] { display: none; }

        /* ---------- Responsive ---------- */
        @media (max-width: 1024px) {
            .grid { 
                grid-template-columns: minmax(0, 1fr) 360px;
                gap: 24px;
            }
            .social-section-left,
            .social-section-right {
                grid-column: auto;
                grid-row: auto;
                order: auto;
            }
            .panel { grid-column: auto; grid-row: auto; order: auto; }
            .side { grid-column: auto; grid-row: auto; order: auto; }
            .hero-body { grid-template-columns: 1fr; gap: 16px; }
            .identity, .hero-actions { padding-top: 0; }
            .hero-actions { flex-direction: row; flex-wrap: wrap; }
            .hero-actions .btn { flex: 1; }
            .social-section { grid-template-columns: 1fr 1fr; gap: 24px; }
            .frames-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 768px) {
            .shell { padding: 20px 16px 50px; }
            .cover { height: 220px; }
            .hero-body { padding: 0 20px 22px; margin-top: -56px; }
            .grid { grid-template-columns: minmax(0, 1fr); }
            .social-section { grid-template-columns: 1fr 1fr; gap: 20px; }
            .frames-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 640px) {
            .shell { padding: 16px 12px 40px; }
            .topbar-inner { padding-left: 12px; padding-right: 12px; }
            .cover { height: 180px; border-radius: 0; }
            .hero { border-radius: 0; }
            .hero-body { padding: 0 12px 18px; margin-top: -48px; gap: 12px; }
            .avatar { width: 112px; height: 112px; font-size: 45px; border-width: 4px; }
            .identity { padding-top: 60px; }
            .identity h1 { font-size: 20px; }
            .identity .handle { font-size: 12px; }
            .identity-meta { gap: 6px; }
            .chip { font-size: 9px; padding: 4px 10px; }
            .hero-actions { gap: 8px; }
            .grid { grid-template-columns: 1fr; }
            .panel { order: 1; grid-column: 1; }
            .side { order: 2; grid-column: 1; }
            .social-section-left { order: 3; grid-column: 1; }
            .social-section-right { order: 4; grid-column: 1; }
            .social-section { grid-template-columns: 1fr; gap: 16px; }
            .frames-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .panel-body { padding: 20px 14px 24px; }
            .row { flex-direction: column; align-items: flex-start; gap: 7px; }
            .row .v { text-align: left; }
            .brand span { display: none; }
            .side { gap: 16px; }
            .card { padding: 18px; }
            .stats { grid-template-columns: repeat(2, 1fr); gap: 8px; }
        }
        @media (max-width: 480px) {
            .shell { padding: 12px 10px 30px; }
            .cover { height: 160px; }
            .hero-body { padding: 0 10px 14px; margin-top: -40px; }
            .avatar { width: 96px; height: 96px; font-size: 40px; }
            .identity h1 { font-size: 18px; }
            .frames-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
        }
    </style>
</head>
<body>

<div class="loader" id="loader">
    <div class="ring"></div>
    <p>Loading profile</p>
</div>

<div class="page" id="page">

    <header class="topbar">
        <div class="topbar-inner">
            <a href="/dashboard" class="brand">
                <img src="/images/app_logo.png" alt="Sohni">
                <span>Sohni</span>
            </a>
            <div class="topbar-actions">
                <a href="/settings" class="btn"><i class="fas fa-cog"></i> Settings</a>
                <a href="/dashboard" class="btn"><i class="fas fa-comments"></i> Back to Chat</a>
                <button class="btn ghost-danger" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </div>
        </div>
    </header>

    <main class="shell">

        <section class="hero">
            <div class="cover" id="cover" onclick="pickCover()">
                <div class="cover-veil">
                    <button type="button" class="cover-edit" aria-label="Change cover">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>
            </div>
            <input type="file" id="coverInput" accept="image/*" onchange="uploadCover()">

            <div class="hero-body">
                <div class="avatar" id="avatar"><i class="fas fa-user"></i></div>

                <div class="identity">
                    <h1 id="pName">—</h1>
                    <div class="handle" id="pEmail">—</div>
                    <div class="identity-meta">
                        <span class="chip free" id="pPlan">Free</span>
                        <span class="chip ok"><i class="fas fa-circle-check"></i> Verified</span>
                        <span class="chip soft"><i class="fas fa-calendar-days"></i> <span id="pSince">—</span></span>
                    </div>
                </div>

                <div class="hero-actions">
                    <button class="btn neon" onclick="editProfile()"><i class="fas fa-pen"></i> Edit Profile</button>
                    <a class="btn" href="/profile/download" download><i class="fas fa-file-pdf"></i> Download Profile</a>
                    <button class="btn" onclick="shareProfile()"><i class="fas fa-share-nodes"></i> Share</button>
                </div>
            </div>
        </section>

        <div class="grid">
 <section class="social-section-left">
            <div class="social-column">
                <div class="social-column-head">
                    <i class="fas fa-user-friends"></i>
                    Recent Friends
                </div>
                <div class="frames-grid" id="friendsGrid">
                    <div class="empty-state"><i class="fas fa-user-plus"></i><p>No friends yet</p></div>
                </div>
            </div>
        </section>
            <section class="panel">
                <nav class="tabs">
                    <button class="tab active" data-tab="about" onclick="switchTab('about')"><i class="fas fa-circle-info"></i> About</button>
                    <button class="tab" data-tab="contact" onclick="switchTab('contact')"><i class="fas fa-address-book"></i> Contact</button>
                    <button class="tab" data-tab="education" onclick="switchTab('education')"><i class="fas fa-graduation-cap"></i> Education</button>
                    <button class="tab" data-tab="experience" onclick="switchTab('experience')"><i class="fas fa-briefcase"></i> Experience</button>
                </nav>

                <div class="panel-body">

                    <div class="tabpane active" id="pane-about">
                        <div class="pane-head">
                            <div class="ico"><i class="fas fa-circle-info"></i></div>
                            <div>
                                <h2>About Me</h2>
                                <div class="sub">Your core account details</div>
                            </div>
                        </div>
                        <div class="rows">
                            <div class="row"><span class="k"><i class="fas fa-signature"></i> Full Name</span><span class="v" id="aName">—</span></div>
                            <div class="row"><span class="k"><i class="fas fa-envelope"></i> Email</span><span class="v" id="aEmail">—</span></div>
                            <div class="row"><span class="k"><i class="fas fa-id-badge"></i> Sohni ID</span><span class="v" id="aSohniId">—</span></div>
                            <div class="row"><span class="k"><i class="fas fa-clock"></i> Member Since</span><span class="v" id="aSince">—</span></div>
                            <div class="row"><span class="k"><i class="fas fa-shield-halved"></i> Status</span><span class="v ok"><i class="fas fa-circle-check"></i> Verified &amp; Active</span></div>
                        </div>
                        <div id="aboutMeDisplay" class="about-copy" style="display: none;"></div>
                    </div>

                    <div class="tabpane" id="pane-contact">
                        <div class="pane-head">
                            <div class="ico"><i class="fas fa-address-book"></i></div>
                            <div>
                                <h2>Contact Information</h2>
                                <div class="sub">Encrypted at rest with AES-256</div>
                            </div>
                        </div>
                        <div class="rows">
                            <div class="row"><span class="k"><i class="fas fa-phone"></i> Phone</span><span class="v" id="cPhone">—</span></div>
                            <div class="row"><span class="k"><i class="fas fa-location-dot"></i> Address</span><span class="v" id="cAddress">—</span></div>
                            <div class="row"><span class="k"><i class="fas fa-lock"></i> Security</span><span class="v ok"><i class="fas fa-shield-halved"></i> Encrypted &amp; Secured</span></div>
                        </div>
                    </div>

                    <div class="tabpane" id="pane-education">
                        <div class="pane-head">
                            <div class="ico"><i class="fas fa-graduation-cap"></i></div>
                            <div>
                                <h2>Education</h2>
                                <div class="sub">Degrees &amp; qualifications</div>
                            </div>
                        </div>
                        <div id="eduList"></div>
                    </div>

                    <div class="tabpane" id="pane-experience">
                        <div class="pane-head">
                            <div class="ico"><i class="fas fa-briefcase"></i></div>
                            <div>
                                <h2>Work Experience</h2>
                                <div class="sub">Professional background</div>
                            </div>
                        </div>
                        <div id="experienceDisplay">
                            <div id="experienceList" class="experience-list"></div>
                        </div>
                    </div>

                </div>
            </section>

            <aside class="side">

                <div class="card">
                    <div class="card-head"><i class="fas fa-chart-simple"></i> Overview</div>
                    <div class="stats">
                        <div class="stat"><b id="sMemberDuration">—</b><small>Member For</small></div>
                        <div class="stat"><b id="sFriendsCount">0</b><small>Friends</small></div>
                        <div class="stat"><b id="sGroupsCount">0</b><small>Groups</small></div>
                        <div class="stat"><b id="sNewFriendsWeek">0</b><small>New This Week</small></div>
                        <div class="stat"><b id="sFollowersCount">0</b><small>Followers</small></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-head"><i class="fas fa-fingerprint"></i> Your Sohni ID</div>
                    <div class="id-box">
                        <small>Unique Identifier</small>
                        <b id="sohniId">—</b>
                    </div>
                    <button class="btn copy-btn" onclick="copyId()"><i class="fas fa-copy"></i> Copy ID</button>
                </div>

                <div class="card">
                    <div class="card-head"><i class="fas fa-qrcode"></i> Connect via QR</div>
                    <div class="qr-frame" id="qrFrame"></div>
                    <p class="qr-hint">Let others scan this to start a chat with you instantly.</p>
                    <div class="qr-actions">
                        <button class="btn" onclick="downloadQR()"><i class="fas fa-download"></i> Save</button>
                        <button class="btn neon" onclick="openScanner()"><i class="fas fa-camera"></i> Scan</button>
                    </div>
                </div>

            </aside>
<section class="social-section-right">

            <div class="social-column">
                <div class="social-column-head">
                    <i class="fas fa-star"></i>
                    Recent Followers
                </div>
                <div class="frames-grid" id="followersGrid">
                    <div class="empty-state"><i class="fas fa-users"></i><p>No followers yet</p></div>
                </div>
            </div>
        </section>
        </div>

    </main>
</div>

<div class="modal" id="scanModal">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="fas fa-qrcode"></i> Scan QR Code</h3>
            <button class="modal-close" onclick="closeScanner()"><i class="fas fa-xmark"></i></button>
        </div>
        <div id="reader"></div>
    </div>
</div>

<div class="toast" id="toast"><i class="fas fa-circle-check"></i> <span id="toastText"></span></div>

<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    let profile = null;
    let scanner = null;

    document.addEventListener('DOMContentLoaded', loadProfile);

    function esc(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    function toast(msg) {
        const t = document.getElementById('toast');
        document.getElementById('toastText').textContent = msg;
        t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), 2600);
    }

    async function loadProfile() {
        try {
            const res = await fetch('/api/profile', { headers: { 'Accept': 'application/json' } });
            if (res.status === 401) { window.location.href = '/account'; return; }
            const json = await res.json();
            if (!json.success) return;

            profile = json.data;
            render();
            buildQR();
            await loadFriendsAndFollowers();
        } catch (e) {
            console.error('Profile load error:', e);
        } finally {
            document.getElementById('loader').classList.add('hidden');
            document.getElementById('page').classList.add('ready');
        }
    }

    async function loadFriendsAndFollowers() {
        try {
            const resFriends = await fetch('/api/friends/recent', { headers: { 'Accept': 'application/json' } });
            const resFollowers = await fetch('/api/followers/recent', { headers: { 'Accept': 'application/json' } });
            
            let friends = [];
            let followers = [];

            if (resFriends.ok) {
                const jsonF = await resFriends.json();
                friends = jsonF.data || [];
            }
            if (resFollowers.ok) {
                const jsonFl = await resFollowers.json();
                followers = jsonFl.data || [];
            }

            renderFriends(friends);
            renderFollowers(followers);
        } catch (e) {
            console.error('Friends/Followers load error:', e);
            renderFriends([]);
            renderFollowers([]);
        }
    }

    function renderFriends(friends) {
        const grid = document.getElementById('friendsGrid');
        if (!friends || friends.length === 0) {
            grid.innerHTML = '<div class="empty-state"><i class="fas fa-user-plus"></i><p>No friends yet</p></div>';
            return;
        }
        const limited = friends.slice(0, 20);
        grid.innerHTML = limited.map(f => createFrameHTML(f, 'friend')).join('');
    }

    function renderFollowers(followers) {
        const grid = document.getElementById('followersGrid');
        if (!followers || followers.length === 0) {
            grid.innerHTML = '<div class="empty-state"><i class="fas fa-users"></i><p>No followers yet</p></div>';
            return;
        }
        const limited = followers.slice(0, 20);
        grid.innerHTML = limited.map(f => createFrameHTML(f, 'follower')).join('');
    }

    function createFrameHTML(person, type) {
        const level = person.sohni_id_type || 'free';
        const bgImg = person.avatar_url ? `background-image: url('${person.avatar_url}');` : '';
        return `
            <div class="frame ${level}">
                <div class="frame-bg" style="${bgImg}"></div>
                <div class="frame-overlay">
                    <div class="frame-name">${esc(person.name || 'Unknown')}</div>
                    <div class="frame-level">
                        ${level === 'premium' ? 'Pro' : level === 'thunder' ? 'Thunder' : 'Free'}
                    </div>
                </div>
            </div>
        `;
    }

    function render() {
        const u = profile;

        document.getElementById('pName').textContent = u.name || '—';
        document.getElementById('pEmail').textContent = u.email || '—';
        document.getElementById('pSince').textContent = u.member_since || '—';

        const premium = u.sohni_id_type === 'premium';
        const plan = document.getElementById('pPlan');
        plan.textContent = premium ? 'Premium' : 'Free';
        plan.className = 'chip ' + (premium ? 'premium' : 'free');

        // Update overview stats
        document.getElementById('sMemberDuration').textContent = u.member_duration || '—';
        document.getElementById('sFriendsCount').textContent = u.friends_count || 0;
        document.getElementById('sGroupsCount').textContent = u.groups_count || 0;
        document.getElementById('sNewFriendsWeek').textContent = u.new_friends_this_week || 0;
        document.getElementById('sFollowersCount').textContent = u.followers_count || 0;

        if (u.avatar_url) {
            document.getElementById('avatar').innerHTML = `<img src="${esc(u.avatar_url)}" alt="Avatar">`;
        }
        if (u.cover_url) {
            document.getElementById('cover').style.backgroundImage = `url('${u.cover_url}')`;
        }

        document.getElementById('aName').textContent = u.name || '—';
        document.getElementById('aEmail').textContent = u.email || '—';
        document.getElementById('aSohniId').textContent = u.sohni_id || '—';
        document.getElementById('aSince').textContent = u.member_since || '—';

        const aboutMe = document.getElementById('aboutMeDisplay');
        if (u.about_me) {
            aboutMe.textContent = u.about_me;
            aboutMe.style.display = 'block';
        }

        const experiences = Array.isArray(u.experiences) ? u.experiences : [];
        const experienceDisplay = document.getElementById('experienceDisplay');
        if (experiences.length) {
            document.getElementById('experienceList').innerHTML = experiences.map(experience => `
                <article class="experience-item">
                    <h3>${esc(experience.title)}</h3>
                    <div class="experience-meta">${esc(experience.company || 'Independent')} ${experience.start_date ? `· ${esc(experience.start_date)}` : ''}${experience.end_date ? ` — ${esc(experience.end_date)}` : experience.start_date ? ' — Present' : ''}</div>
                    ${experience.description ? `<p>${esc(experience.description)}</p>` : ''}
                </article>`).join('');
        } else {
            document.getElementById('experienceList').innerHTML = '<div class="empty"><i class="fas fa-briefcase"></i><p>No work experience added yet</p></div>';
        }
        experienceDisplay.style.display = 'block';

        document.getElementById('cPhone').textContent = u.phone || '—';
        document.getElementById('cAddress').textContent = u.address || '—';

        document.getElementById('sohniId').textContent = u.sohni_id || '—';

        const edu = u.educations || [];
        document.getElementById('eduList').innerHTML = edu.length
            ? edu.map(d => `
                <div class="edu">
                    <div class="edu-title"><i class="fas fa-certificate"></i> ${esc(d.title)}</div>
                    <div class="edu-meta">
                        ${d.completion_date ? `<span><i class="fas fa-calendar"></i> ${esc(d.completion_date)}</span>` : ''}
                        ${d.grade ? `<span><i class="fas fa-star"></i> ${esc(d.grade)}</span>` : ''}
                        ${d.marks ? `<span><i class="fas fa-chart-simple"></i> ${esc(d.marks)}</span>` : ''}
                    </div>
                </div>`).join('')
            : `<div class="empty"><i class="fas fa-book-open"></i><p>No education records added yet</p></div>`;
    }

    function switchTab(name) {
        document.querySelectorAll('.tab').forEach(b => b.classList.toggle('active', b.dataset.tab === name));
        document.querySelectorAll('.tabpane').forEach(p => p.classList.toggle('active', p.id === 'pane-' + name));
    }

    function buildQR() {
        if (!profile) return;
        const frame = document.getElementById('qrFrame');
        frame.innerHTML = '';
        new QRCode(frame, {
            text: `${window.location.origin}/profile?user_id=${profile.id}&sohni_id=${encodeURIComponent(profile.sohni_id || '')}`,
            width: 190,
            height: 190,
            colorDark: '#0084ff',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
    }

    function downloadQR() {
        const canvas = document.querySelector('#qrFrame canvas');
        if (!canvas) return;
        const a = document.createElement('a');
        a.href = canvas.toDataURL('image/png');
        a.download = `sohni-${profile.sohni_id || 'qr'}.png`;
        a.click();
        toast('QR code downloaded');
    }

    function copyId() {
        if (!profile?.sohni_id) return;
        navigator.clipboard.writeText(profile.sohni_id).then(() => toast('Sohni ID copied'));
    }

    function pickCover() {
        document.getElementById('coverInput').click();
    }

    async function uploadCover() {
        const input = document.getElementById('coverInput');
        const file = input.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = e => { document.getElementById('cover').style.backgroundImage = `url('${e.target.result}')`; };
        reader.readAsDataURL(file);

        const fd = new FormData();
        fd.append('cover_image', file);

        try {
            const res = await fetch('/api/profile/update', {
                method: 'POST',
                body: fd,
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
            });
            const json = await res.json();
            if (!res.ok || !json.success) throw new Error(json.message || 'Upload failed');
            if (json.data?.cover_url) {
                document.getElementById('cover').style.backgroundImage = `url('${json.data.cover_url}')`;
            }
            toast('Cover photo updated');
        } catch (e) {
            console.error(e);
            alert('Could not save cover image. Please try again.');
        } finally {
            input.value = '';
        }
    }

    function openScanner() {
        document.getElementById('scanModal').classList.add('active');
        if (!scanner) {
            scanner = new Html5QrcodeScanner('reader', { fps: 10, qrbox: 250 }, false);
            scanner.render(onScanSuccess, () => {});
        }
    }

    function closeScanner() {
        document.getElementById('scanModal').classList.remove('active');
        if (scanner) { scanner.clear(); scanner = null; }
    }

    function onScanSuccess(text) {
        try {
            const url = new URL(text);
            const userId = url.searchParams.get('user_id');
            if (userId) { closeScanner(); sendChatRequest(userId); }
        } catch (e) {
            console.warn('Unrecognised QR payload');
        }
    }

    async function sendChatRequest(userId) {
        try {
            const res = await fetch('/api/chat/request', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ user_id: userId })
            });
            const json = await res.json();
            if (json.success) {
                window.location.href = '/dashboard';
            } else {
                alert(json.message || 'Failed to send chat request');
            }
        } catch (e) {
            console.error(e);
            alert('Error sending chat request');
        }
    }

    function editProfile() {
        window.location.href = '/edit-profile';
    }

    function shareProfile() {
        const url = window.location.origin + '/profile';
        if (navigator.share) {
            navigator.share({ title: 'My Sohni Profile', text: 'Connect with me on Sohni', url });
        } else {
            navigator.clipboard.writeText(url).then(() => toast('Profile link copied'));
        }
    }

    async function logout() {
        await fetch('/api/auth/logout', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        });
        window.location.href = '/account';
    }

    document.getElementById('scanModal').addEventListener('click', e => {
        if (e.target.id === 'scanModal') closeScanner();
    });
</script>

</body>
</html>
