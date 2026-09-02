<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Activate Account — Sohni</title>
    <link rel="icon" type="image/png" href="/images/app_logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
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
            --success: #10b981;
            --ease: cubic-bezier(0.22, 1, 0.36, 1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background:
                radial-gradient(ellipse 60% 50% at 85% 8%, rgba(0, 194, 255, 0.14), transparent 60%),
                radial-gradient(ellipse 50% 45% at 8% 90%, rgba(0, 132, 255, 0.10), transparent 60%),
                linear-gradient(180deg, #ffffff 0%, #f2f9ff 60%, #ffffff 100%);
            color: var(--ink);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        h1 { font-family: 'Sora', sans-serif; }

        .card {
            background: var(--white);
            border-radius: 26px;
            padding: 48px 42px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(11, 21, 38, 0.12);
            animation: rise 0.6s var(--ease);
        }

        @keyframes rise {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo {
            width: 84px;
            height: 84px;
            margin-bottom: 22px;
            filter: drop-shadow(0 0 18px rgba(0, 194, 255, 0.6));
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.06); }
        }

        h1 {
            font-size: 26px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .sub {
            font-size: 14px;
            color: var(--ink-dim);
            line-height: 1.7;
            margin-bottom: 26px;
        }

        .email-chip {
            display: inline-block;
            background: rgba(0, 194, 255, 0.08);
            border: 1px solid rgba(0, 194, 255, 0.25);
            color: var(--neon-deep);
            font-weight: 600;
            font-size: 14px;
            border-radius: 100px;
            padding: 10px 22px;
            margin-bottom: 30px;
        }

        .btn-activate {
            width: 100%;
            padding: 17px;
            border: none;
            border-radius: 100px;
            font-family: 'Sora', sans-serif;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #fff;
            background: linear-gradient(135deg, var(--neon), var(--neon-deep));
            box-shadow: 0 10px 30px var(--neon-glow);
            cursor: pointer;
            transition: all 0.3s var(--ease);
        }

        .btn-activate:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 14px 38px var(--neon-glow);
        }

        .btn-activate:disabled { opacity: 0.6; cursor: not-allowed; }

        .secure-note {
            margin-top: 22px;
            font-size: 12px;
            color: var(--ink-faint);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .error-box {
            display: none;
            margin-top: 18px;
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #b91c1c;
            font-size: 13px;
            border-radius: 12px;
            padding: 12px 16px;
        }

        @media (max-width: 520px) {
            .card { padding: 36px 24px; }
            h1 { font-size: 22px; }
        }
    </style>
</head>
<body>
    <div class="card">
        <img src="/images/app_logo.png" alt="Sohni" class="logo">
        <h1>Activate Your Account</h1>
        <p class="sub">You're almost there! Confirm that this is your email and press the button below to verify your Sohni account.</p>

        <div class="email-chip">📧 {{ $user->email }}</div>

        <form method="POST" action="{{ $actionUrl }}" id="activateForm">
            @csrf
            <button type="submit" class="btn-activate" id="activateBtn">✓ Verify Your Account</button>
        </form>

        <div class="error-box" id="errorBox"></div>

        <div class="secure-note">🔒 Secured with a signed, single-use link — expires 60 minutes after it was sent.</div>
    </div>

    <script>
        document.getElementById('activateForm').addEventListener('submit', function() {
            const btn = document.getElementById('activateBtn');
            btn.disabled = true;
            btn.textContent = 'Activating...';
        });
    </script>
</body>
</html>
