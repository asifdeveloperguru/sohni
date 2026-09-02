<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sohni — the chat app made for Pakistani locals. Fast, secure, and beautifully simple.">
    <title>Sohni — Chat App for Pakistani Locals</title>
    <link rel="icon" type="image/png" href="/images/app_logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --white: #ffffff;
            --bg-soft: #f4f9ff;
            --neon: #00c2ff;
            --neon-deep: #0084ff;
            --neon-glow: rgba(0, 194, 255, 0.45);
            --ink: #0b1526;
            --ink-dim: #4a5a70;
            --ink-faint: #8194ab;
            --card: #ffffff;
            --border: rgba(11, 21, 38, 0.08);
            --border-neon: rgba(0, 194, 255, 0.35);
            --radius: 20px;
            --ease: cubic-bezier(0.22, 1, 0.36, 1);
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--white);
            color: var(--ink);
            overflow-x: hidden;
            line-height: 1.6;
        }

        h1, h2, h3, .logo-text, .btn { font-family: 'Sora', sans-serif; }

        /* ============ Background ============ */
        .bg-scene {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
            background:
                radial-gradient(ellipse 60% 50% at 85% 8%, rgba(0, 194, 255, 0.14), transparent 60%),
                radial-gradient(ellipse 50% 45% at 8% 90%, rgba(0, 132, 255, 0.10), transparent 60%),
                linear-gradient(180deg, #ffffff 0%, #f2f9ff 50%, #ffffff 100%);
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.35;
            animation: drift 14s ease-in-out infinite alternate;
        }

        .orb1 { width: 500px; height: 500px; background: radial-gradient(circle, #7fdcff, transparent 70%); top: -180px; right: -100px; }
        .orb2 { width: 420px; height: 420px; background: radial-gradient(circle, #b3e9ff, transparent 70%); bottom: -140px; left: -110px; animation-delay: 4s; }

        @keyframes drift {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(-45px, 35px) scale(1.1); }
        }

        .grid-overlay {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(0, 132, 255, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 132, 255, 0.05) 1px, transparent 1px);
            background-size: 56px 56px;
            mask-image: radial-gradient(ellipse 90% 60% at 50% 0%, black 30%, transparent 100%);
            -webkit-mask-image: radial-gradient(ellipse 90% 60% at 50% 0%, black 30%, transparent 100%);
        }

        /* ============ Nav ============ */
        nav {
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px clamp(20px, 5vw, 60px);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            background: rgba(255, 255, 255, 0.8);
            border-bottom: 1px solid var(--border);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .brand img {
            width: 44px;
            height: 44px;
            object-fit: contain;
            filter: drop-shadow(0 4px 14px rgba(0, 194, 255, 0.35));
            transition: transform 0.4s var(--ease);
        }

        .brand:hover img { transform: rotate(-8deg) scale(1.08); }

        .logo-text {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(120deg, #0b1526 20%, var(--neon-deep) 60%, var(--neon));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-links {
            display: flex;
            gap: 36px;
            list-style: none;
            align-items: center;
        }

        .nav-links a {
            color: var(--ink-dim);
            text-decoration: none;
            font-size: 14.5px;
            font-weight: 500;
            transition: color 0.3s;
            position: relative;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -6px;
            width: 0;
            height: 2px;
            border-radius: 2px;
            background: linear-gradient(90deg, var(--neon), var(--neon-deep));
            transition: width 0.35s var(--ease);
        }

        .nav-links a:hover { color: var(--ink); }
        .nav-links a:hover::after { width: 100%; }

        .nav-cta {
            padding: 10px 24px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--neon), var(--neon-deep));
            color: #fff !important;
            font-weight: 600;
            box-shadow: 0 6px 22px var(--neon-glow);
            transition: transform 0.3s var(--ease), box-shadow 0.3s var(--ease);
        }

        .nav-cta::after { display: none; }
        .nav-cta:hover { transform: translateY(-2px); box-shadow: 0 10px 30px var(--neon-glow); }

        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            padding: 8px;
            z-index: 110;
        }

        .hamburger span {
            width: 24px;
            height: 2.5px;
            border-radius: 2px;
            background: var(--ink);
            transition: 0.35s var(--ease);
        }

        .hamburger.open span:nth-child(1) { transform: translateY(7.5px) rotate(45deg); }
        .hamburger.open span:nth-child(2) { opacity: 0; }
        .hamburger.open span:nth-child(3) { transform: translateY(-7.5px) rotate(-45deg); }

        /* ============ Layout ============ */
        .wrap {
            position: relative;
            z-index: 5;
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 clamp(20px, 4vw, 40px);
        }

        /* ============ Hero ============ */
        .hero {
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: clamp(40px, 6vw, 90px);
            align-items: center;
            padding: clamp(60px, 9vh, 110px) 0 clamp(50px, 8vh, 90px);
            min-height: calc(100vh - 80px);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 16px;
            border-radius: 100px;
            background: rgba(0, 194, 255, 0.1);
            border: 1px solid var(--border-neon);
            color: var(--neon-deep);
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 26px;
            animation: rise 0.8s var(--ease) both;
        }

        .badge .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--neon);
            box-shadow: 0 0 10px var(--neon);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }

        .hero h1 {
            font-size: clamp(38px, 5.4vw, 68px);
            font-weight: 800;
            line-height: 1.08;
            letter-spacing: -2px;
            margin-bottom: 24px;
            color: var(--ink);
            animation: rise 0.8s var(--ease) 0.1s both;
        }

        .hero h1 .grad {
            background: linear-gradient(120deg, var(--neon), var(--neon-deep) 50%, #00e0ff);
            background-size: 200% auto;
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shimmer 5s linear infinite;
        }

        @keyframes shimmer {
            to { background-position: 200% center; }
        }

        .hero-sub {
            font-size: clamp(15px, 1.6vw, 18px);
            color: var(--ink-dim);
            max-width: 520px;
            margin-bottom: 38px;
            font-weight: 300;
            animation: rise 0.8s var(--ease) 0.2s both;
        }

        @keyframes rise {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .hero-actions {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 46px;
            animation: rise 0.8s var(--ease) 0.3s both;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 34px;
            border-radius: 14px;
            font-size: 15.5px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: transform 0.35s var(--ease), box-shadow 0.35s var(--ease), background 0.35s;
        }

        .btn-solid {
            background: linear-gradient(135deg, var(--neon), var(--neon-deep));
            color: #fff;
            box-shadow: 0 10px 32px var(--neon-glow);
        }

        .btn-solid:hover { transform: translateY(-3px); box-shadow: 0 16px 44px var(--neon-glow); }

        .btn-ghost {
            background: var(--white);
            color: var(--ink);
            border: 1.5px solid var(--border);
            box-shadow: 0 4px 16px rgba(11, 21, 38, 0.05);
        }

        .btn-ghost:hover {
            transform: translateY(-3px);
            border-color: var(--border-neon);
            box-shadow: 0 10px 28px rgba(0, 194, 255, 0.18);
        }

        .trust-row {
            display: flex;
            align-items: center;
            gap: 18px;
            animation: rise 0.8s var(--ease) 0.4s both;
        }

        .avatars { display: flex; }

        .avatars span {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 2.5px solid var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            margin-left: -10px;
            background: linear-gradient(135deg, #d6f4ff, #aee7ff);
            box-shadow: 0 3px 10px rgba(0, 132, 255, 0.15);
        }

        .avatars span:first-child { margin-left: 0; }

        .trust-row p { font-size: 13.5px; color: var(--ink-faint); }
        .trust-row strong { color: var(--neon-deep); }

        /* ============ Phone ============ */
        .hero-visual {
            position: relative;
            display: flex;
            justify-content: center;
            animation: rise 0.9s var(--ease) 0.25s both;
        }

        .phone-glow {
            position: absolute;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(0, 194, 255, 0.22), transparent 68%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: pulse 4s ease-in-out infinite;
        }

        .float-chip {
            position: absolute;
            padding: 12px 18px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid var(--border-neon);
            backdrop-filter: blur(12px);
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
            display: flex;
            align-items: center;
            gap: 9px;
            box-shadow: 0 14px 36px rgba(0, 132, 255, 0.16);
            z-index: 12;
            animation: hover-float 5s ease-in-out infinite;
        }

        .chip1 { top: 12%; left: -4%; animation-delay: 0s; }
        .chip2 { bottom: 18%; right: -6%; animation-delay: 2.5s; }

        @keyframes hover-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-14px); }
        }

        .phone {
            width: clamp(270px, 24vw, 320px);
            aspect-ratio: 9 / 18.5;
            border-radius: 46px;
            padding: 11px;
            background: linear-gradient(160deg, #eaf6ff, #ffffff);
            border: 1px solid var(--border-neon);
            box-shadow: 0 45px 90px rgba(11, 21, 38, 0.18), 0 0 60px rgba(0, 194, 255, 0.2);
            position: relative;
            z-index: 10;
            animation: hover-float 6s ease-in-out infinite;
        }

        .phone::before {
            content: '';
            position: absolute;
            top: 11px;
            left: 50%;
            transform: translateX(-50%);
            width: 105px;
            height: 26px;
            background: #0b1526;
            border-radius: 0 0 18px 18px;
            z-index: 15;
        }

        .screen {
            width: 100%;
            height: 100%;
            border-radius: 38px;
            overflow: hidden;
            background: var(--bg-soft);
            display: flex;
            flex-direction: column;
        }

        .chat-top {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 40px 18px 14px;
            background: linear-gradient(135deg, rgba(0, 194, 255, 0.12), rgba(0, 132, 255, 0.08));
            border-bottom: 1px solid var(--border);
        }

        .chat-top img { width: 32px; height: 32px; object-fit: contain; }

        .chat-top .name { font-weight: 600; font-size: 14px; color: var(--ink); }
        .chat-top .status { font-size: 11px; color: #10b981; display: flex; align-items: center; gap: 5px; }
        .chat-top .status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: #10b981; }

        .chat-body {
            flex: 1;
            padding: 18px 14px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .bubble {
            max-width: 78%;
            padding: 11px 15px;
            border-radius: 17px;
            font-size: 12.5px;
            line-height: 1.5;
            opacity: 0;
            animation: pop 0.55s var(--ease) forwards;
        }

        .bubble:nth-child(1) { animation-delay: 0.5s; }
        .bubble:nth-child(2) { animation-delay: 1.0s; }
        .bubble:nth-child(3) { animation-delay: 1.5s; }
        .bubble:nth-child(4) { animation-delay: 2.0s; }

        @keyframes pop {
            from { opacity: 0; transform: translateY(12px) scale(0.92); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .in {
            align-self: flex-start;
            background: var(--white);
            border: 1px solid var(--border);
            color: var(--ink);
            border-bottom-left-radius: 5px;
            box-shadow: 0 3px 10px rgba(11, 21, 38, 0.06);
        }

        .out {
            align-self: flex-end;
            background: linear-gradient(135deg, var(--neon), var(--neon-deep));
            color: #fff;
            border-bottom-right-radius: 5px;
            box-shadow: 0 6px 16px rgba(0, 194, 255, 0.35);
        }

        .chat-input {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0 12px 16px;
            padding: 11px 16px;
            border-radius: 100px;
            background: var(--white);
            border: 1px solid var(--border);
            font-size: 12px;
            color: var(--ink-faint);
            box-shadow: 0 3px 10px rgba(11, 21, 38, 0.05);
        }

        .chat-input .send {
            margin-left: auto;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--neon), var(--neon-deep));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
        }

        /* ============ Sections ============ */
        section { padding: clamp(60px, 10vh, 110px) 0; }

        .sec-head {
            text-align: center;
            max-width: 640px;
            margin: 0 auto clamp(40px, 6vh, 70px);
        }

        .sec-tag {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 100px;
            background: rgba(0, 194, 255, 0.1);
            border: 1px solid var(--border-neon);
            color: var(--neon-deep);
            font-size: 12.5px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 18px;
        }

        .sec-head h2 {
            font-size: clamp(28px, 3.6vw, 44px);
            font-weight: 800;
            letter-spacing: -1px;
            line-height: 1.15;
            margin-bottom: 16px;
            color: var(--ink);
        }

        .sec-head p { color: var(--ink-dim); font-size: clamp(14px, 1.5vw, 16.5px); font-weight: 300; }

        /* Stats */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .stat {
            text-align: center;
            padding: 36px 20px;
            border-radius: var(--radius);
            background: var(--card);
            border: 1px solid var(--border);
            box-shadow: 0 6px 22px rgba(11, 21, 38, 0.05);
            transition: transform 0.4s var(--ease), border-color 0.4s, box-shadow 0.4s;
        }

        .stat:hover {
            transform: translateY(-8px);
            border-color: var(--border-neon);
            box-shadow: 0 22px 48px rgba(0, 194, 255, 0.18);
        }

        .stat .num {
            font-family: 'Sora', sans-serif;
            font-size: clamp(34px, 4vw, 48px);
            font-weight: 800;
            background: linear-gradient(120deg, var(--neon-deep), var(--neon));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat .lbl { font-size: 14px; color: var(--ink-dim); margin-top: 8px; }

        /* Feature cards */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
        }

        .card {
            position: relative;
            padding: 36px 30px;
            border-radius: var(--radius);
            background: var(--card);
            border: 1px solid var(--border);
            box-shadow: 0 6px 22px rgba(11, 21, 38, 0.05);
            overflow: hidden;
            transition: transform 0.45s var(--ease), border-color 0.45s, box-shadow 0.45s;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--neon), transparent);
            opacity: 0;
            transition: opacity 0.45s;
        }

        .card:hover {
            transform: translateY(-10px);
            border-color: var(--border-neon);
            box-shadow: 0 28px 60px rgba(0, 194, 255, 0.18);
        }

        .card:hover::before { opacity: 1; }

        .card .icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 27px;
            background: linear-gradient(135deg, rgba(0, 194, 255, 0.12), rgba(0, 132, 255, 0.08));
            border: 1px solid var(--border-neon);
            margin-bottom: 22px;
        }

        .card h3 { font-size: 19px; font-weight: 700; margin-bottom: 10px; color: var(--ink); }
        .card p { font-size: 14px; color: var(--ink-dim); font-weight: 300; }

        /* CTA */
        .cta-panel {
            position: relative;
            text-align: center;
            padding: clamp(50px, 8vh, 90px) clamp(24px, 5vw, 70px);
            border-radius: 28px;
            background: linear-gradient(150deg, #06121f 0%, #0a2540 55%, #063a5c 100%);
            border: 1px solid rgba(0, 194, 255, 0.4);
            overflow: hidden;
            color: #fff;
        }

        .cta-panel::before {
            content: '';
            position: absolute;
            width: 480px;
            height: 480px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(0, 194, 255, 0.3), transparent 65%);
            top: -240px;
            left: 50%;
            transform: translateX(-50%);
            pointer-events: none;
        }

        .cta-panel img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin-bottom: 24px;
            filter: drop-shadow(0 0 26px rgba(0, 194, 255, 0.75));
            animation: hover-float 4.5s ease-in-out infinite;
        }

        .cta-panel h2 {
            font-size: clamp(28px, 4vw, 46px);
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 16px;
        }

        .cta-panel p {
            color: rgba(255, 255, 255, 0.7);
            max-width: 520px;
            margin: 0 auto 36px;
            font-size: clamp(14px, 1.5vw, 16.5px);
            font-weight: 300;
        }

        /* Footer */
        footer {
            border-top: 1px solid var(--border);
            padding: 36px clamp(20px, 5vw, 60px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
            position: relative;
            z-index: 5;
            background: rgba(255, 255, 255, 0.7);
        }

        footer .brand img { width: 34px; height: 34px; }
        footer .logo-text { font-size: 20px; }
        footer p { font-size: 13px; color: var(--ink-faint); }

        /* Reveal on scroll */
        .reveal {
            opacity: 0;
            transform: translateY(36px);
            transition: opacity 0.8s var(--ease), transform 0.8s var(--ease);
        }

        .reveal.shown { opacity: 1; transform: translateY(0); }

        /* ============ Responsive ============ */
        @media (max-width: 1024px) {
            .hero { grid-template-columns: 1fr; text-align: center; min-height: auto; }
            .hero-sub { margin-left: auto; margin-right: auto; }
            .hero-actions, .trust-row { justify-content: center; }
            .hero-visual { margin-top: 20px; }
            .chip1 { left: 4%; }
            .chip2 { right: 4%; }
        }

        @media (max-width: 768px) {
            .nav-links {
                position: fixed;
                inset: 0;
                background: rgba(255, 255, 255, 0.97);
                backdrop-filter: blur(24px);
                flex-direction: column;
                justify-content: center;
                gap: 34px;
                font-size: 18px;
                transform: translateX(100%);
                transition: transform 0.45s var(--ease);
                z-index: 105;
            }

            .nav-links.open { transform: translateX(0); }
            .nav-links a { font-size: 18px; }
            .hamburger { display: flex; }
        }

        @media (max-width: 520px) {
            .hero h1 { letter-spacing: -1px; }
            .hero-actions { flex-direction: column; }
            .btn { width: 100%; justify-content: center; }
            .float-chip { display: none; }
            .phone { width: min(280px, 82vw); }
            .stats { grid-template-columns: 1fr 1fr; }
            .trust-row { flex-direction: column; gap: 12px; }
            footer { justify-content: center; text-align: center; }
        }

        @media (min-width: 1600px) {
            .wrap { max-width: 1360px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
        }
    </style>
</head>
<body>
    <div class="bg-scene">
        <div class="orb orb1"></div>
        <div class="orb orb2"></div>
        <div class="grid-overlay"></div>
    </div>

    <nav>
        <a href="#" class="brand">
            <img src="/images/app_logo.png" alt="Sohni logo">
            <span class="logo-text">Sohni</span>
        </a>
        <ul class="nav-links" id="navLinks">
            <li><a href="#features">Features</a></li>
            <li><a href="#stats">Community</a></li>
            <li><a href="#download">Download</a></li>
            <li><a href="/account" class="nav-cta">Sign In</a></li>
        </ul>
        <div class="hamburger" id="hamburger">
            <span></span><span></span><span></span>
        </div>
    </nav>

    <div class="wrap">
        <!-- Hero -->
        <div class="hero">
            <div class="hero-content">
                <div class="badge"><span class="dot"></span> Now connecting 50+ cities across Pakistan</div>
                <h1>Where Pakistan <span class="grad">Comes to Chat</span></h1>
                <p class="hero-sub">Sohni is the messaging app built for Pakistani locals — blazing fast on any network, protected with end-to-end encryption, and designed to bring your community closer.</p>
                <div class="hero-actions">
                    <a href="/account" class="btn btn-solid">📲 Get Started Free</a>
                    <a href="#features" class="btn btn-ghost">Explore Features →</a>
                </div>
                <div class="trust-row">
                    <div class="avatars">
                        <span>🧕</span><span>👨</span><span>👩</span><span>🧔</span>
                    </div>
                    <p><strong>10,000+</strong> locals already chatting on Sohni</p>
                </div>
            </div>

            <div class="hero-visual">
                <div class="phone-glow"></div>
                <div class="float-chip chip1">🔒 End-to-End Encrypted</div>
                <div class="float-chip chip2">⚡ Works on 2G/3G/4G</div>
                <div class="phone">
                    <div class="screen">
                        <div class="chat-top">
                            <img src="/images/app_logo.png" alt="">
                            <div>
                                <div class="name">Lahore Foodies 🍛</div>
                                <div class="status">142 online</div>
                            </div>
                        </div>
                        <div class="chat-body">
                            <div class="bubble in">Assalamualaikum! Best nihari spot in Lahore? 🤔</div>
                            <div class="bubble out">Waris Nihari, hands down! 🔥</div>
                            <div class="bubble in">Meetup this Sunday? Who's in? 🙌</div>
                            <div class="bubble out">Count me in! InshaAllah 🇵🇰</div>
                        </div>
                        <div class="chat-input">Type a message... <span class="send">➤</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <section id="stats">
            <div class="stats">
                <div class="stat reveal"><div class="num">10K+</div><div class="lbl">Active Users</div></div>
                <div class="stat reveal"><div class="num">50+</div><div class="lbl">Cities Connected</div></div>
                <div class="stat reveal"><div class="num">1M+</div><div class="lbl">Messages Sent</div></div>
                <div class="stat reveal"><div class="num">99.9%</div><div class="lbl">Uptime</div></div>
            </div>
        </section>

        <!-- Features -->
        <section id="features">
            <div class="sec-head reveal">
                <span class="sec-tag">Features</span>
                <h2>Everything you need, nothing you don't</h2>
                <p>Built from the ground up for how Pakistanis actually chat — in groups, with family, and across every network condition.</p>
            </div>
            <div class="cards">
                <div class="card reveal">
                    <div class="icon">💬</div>
                    <h3>Real-time Messaging</h3>
                    <p>Instant delivery with zero lag. Texts, voice notes, photos, and files — all lightning quick.</p>
                </div>
                <div class="card reveal">
                    <div class="icon">🔒</div>
                    <h3>End-to-End Encryption</h3>
                    <p>Your conversations belong to you. Nobody else can read them — not even us.</p>
                </div>
                <div class="card reveal">
                    <div class="icon">📍</div>
                    <h3>Local Communities</h3>
                    <p>Join groups by city, neighborhood, or interest. From Karachi to Khyber, find your people.</p>
                </div>
                <div class="card reveal">
                    <div class="icon">⚡</div>
                    <h3>Built for Every Network</h3>
                    <p>Optimized for Pakistani networks — smooth even on 2G, with minimal data usage.</p>
                </div>
                <div class="card reveal">
                    <div class="icon">🗣️</div>
                    <h3>Urdu-First Design</h3>
                    <p>Full Urdu support with Roman Urdu smart suggestions. Chat the way you speak.</p>
                </div>
                <div class="card reveal">
                    <div class="icon">👨‍👩‍👧‍👦</div>
                    <h3>Family Groups</h3>
                    <p>Large group support with voice notes, event planning, and shared albums for the whole khandaan.</p>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section id="download">
            <div class="cta-panel reveal">
                <img src="/images/app_logo.png" alt="Sohni logo">
                <h2>Join the Sohni Community</h2>
                <p>Thousands of Pakistanis are already connecting, sharing, and building communities. Sign up free — create your account in minutes.</p>
                <a href="/account" class="btn btn-solid" id="downloadBtn">🚀 Create Your Account</a>
            </div>
        </section>
    </div>

    <footer>
        <a href="#" class="brand">
            <img src="/images/app_logo.png" alt="Sohni logo">
            <span class="logo-text">Sohni</span>
        </a>
        <p>© 2026 Sohni. Made with ❤️ for Pakistan 🇵🇰</p>
    </footer>

    <script>
        // Mobile menu
        const hamburger = document.getElementById('hamburger');
        const navLinks = document.getElementById('navLinks');

        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('open');
            navLinks.classList.toggle('open');
        });

        navLinks.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
            hamburger.classList.remove('open');
            navLinks.classList.remove('open');
        }));

        // Reveal on scroll
        const io = new IntersectionObserver(entries => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.classList.add('shown');
                    io.unobserve(e.target);
                }
            });
        }, { threshold: 0.12 });

        document.querySelectorAll('.reveal').forEach(el => io.observe(el));

        // Download CTA
        document.getElementById('downloadBtn').addEventListener('click', e => {
            e.preventDefault();
            alert('Sohni is coming soon to iOS and Android! 📲');
        });
    </script>
</body>
</html>
