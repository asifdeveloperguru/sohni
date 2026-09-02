<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify Email — Sohni</title>
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
            --radius: 20px;
            --ease: cubic-bezier(0.22, 1, 0.36, 1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--white) 0%, var(--bg-soft) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        h1, h2 { font-family: 'Sora', sans-serif; }

        .verify-card {
            width: 100%;
            max-width: 480px;
            background: var(--white);
            border-radius: 24px;
            padding: clamp(30px, 6vw, 50px);
            box-shadow: 0 10px 40px rgba(11, 21, 38, 0.1);
            text-align: center;
        }

        .verify-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(0, 194, 255, 0.15), rgba(0, 132, 255, 0.1));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 48px;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(0, 194, 255, 0.3); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(0, 194, 255, 0); }
        }

        h1 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 12px;
            color: var(--ink);
        }

        .verify-text {
            font-size: 14px;
            color: var(--ink-dim);
            line-height: 1.8;
            margin-bottom: 32px;
        }

        .email-display {
            background: linear-gradient(135deg, rgba(0, 194, 255, 0.1), rgba(0, 132, 255, 0.08));
            border: 1px solid rgba(0, 194, 255, 0.3);
            padding: 14px 16px;
            border-radius: 12px;
            font-weight: 600;
            color: var(--neon-deep);
            font-size: 14px;
            margin-bottom: 32px;
            word-break: break-all;
        }

        .btn {
            width: 100%;
            padding: 14px 24px;
            border-radius: 12px;
            font-family: 'Sora', sans-serif;
            font-size: 16px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: transform 0.3s var(--ease), box-shadow 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--neon), var(--neon-deep));
            color: #fff;
            box-shadow: 0 8px 24px var(--neon-glow);
            margin-bottom: 14px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px var(--neon-glow);
        }

        .btn-secondary {
            background: var(--white);
            color: var(--neon-deep);
            border: 2px solid var(--neon);
        }

        .btn-secondary:hover {
            background: rgba(0, 194, 255, 0.05);
            transform: translateY(-2px);
        }

        .status-pending {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 12px;
            font-size: 13px;
            color: #3b82f6;
            margin-top: 28px;
            font-weight: 500;
        }

        .status-pending::before {
            content: '';
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #3b82f6;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        .status-verified {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 12px;
            font-size: 13px;
            color: var(--success);
            margin-top: 28px;
            font-weight: 500;
        }

        .status-verified::before {
            content: '✓';
            font-weight: 700;
        }

        .resend-section {
            margin-top: 28px;
            padding-top: 28px;
            border-top: 1px solid rgba(11, 21, 38, 0.1);
        }

        .resend-text {
            font-size: 13px;
            color: var(--ink-dim);
            margin-bottom: 14px;
        }

        .resend-btn {
            background: none;
            border: none;
            color: var(--neon-deep);
            font-weight: 700;
            cursor: pointer;
            text-decoration: underline;
            font-size: 13px;
            transition: color 0.3s;
        }

        .resend-btn:hover {
            color: var(--neon);
        }

        .resend-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        @media (max-width: 520px) {
            .verify-card {
                padding: 30px 20px;
            }

            h1 {
                font-size: 24px;
            }

            .verify-icon {
                width: 80px;
                height: 80px;
                font-size: 40px;
                margin-bottom: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="verify-card">
        <div class="verify-icon" id="verifyIcon">📧</div>

        <h1 id="verifyTitle">Check Your Email</h1>
        <p class="verify-text">We've sent a verification link to your email address. Click the link to verify and continue setting up your profile.</p>

        <div class="email-display" id="emailDisplay">you@gmail.com</div>

        <button class="btn btn-primary" id="verifyBtn" onclick="checkEmailVerified()">
            ✓ Email Verified? Click Here
        </button>
        <button class="btn btn-secondary" onclick="window.location.href='/account'">← Go Back to Sign In</button>

        <div id="verifyStatus" class="status-pending">Waiting for verification...</div>

        <div class="resend-section">
            <p class="resend-text">Didn't receive email?</p>
            <button class="resend-btn" id="resendBtn" onclick="resendVerificationEmail()">Resend Link</button>
            <span id="resendTimer" style="font-size: 12px; color: var(--ink-faint); margin-left: 10px;"></span>
        </div>
    </div>

    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
        let verificationStatus = 'pending';
        let resendCooldown = 0;
        let pollTimer = null;

        document.addEventListener('DOMContentLoaded', function() {
            loadStatus();
            // Realtime: poll verification status every 4 seconds
            pollTimer = setInterval(loadStatus, 4000);

            // If redirected back from the email link
            if (new URLSearchParams(window.location.search).get('verified') === '1') {
                markVerified();
            }
        });

        async function loadStatus() {
            try {
                const res = await fetch('/api/auth/verification-status', {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.status === 401) { window.location.href = '/account'; return; }
                const json = await res.json();
                if (json.success) {
                    document.getElementById('emailDisplay').textContent = json.data.email;
                    if (json.data.verified) markVerified();
                }
            } catch (e) { /* server unreachable; retry on next poll */ }
        }

        function markVerified() {
            if (verificationStatus === 'verified') return;
            verificationStatus = 'verified';
            if (pollTimer) clearInterval(pollTimer);

            document.getElementById('verifyIcon').textContent = '✓';
            document.getElementById('verifyIcon').style.animation = 'none';
            document.getElementById('verifyIcon').style.background = 'linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.1))';

            document.getElementById('verifyTitle').textContent = 'Email Verified!';
            document.getElementById('verifyStatus').innerHTML = '<span>✓ Email verified successfully</span>';
            document.getElementById('verifyStatus').className = 'status-verified';

            document.getElementById('verifyBtn').textContent = '→ Continue to Profile Setup';
            document.getElementById('verifyBtn').style.background = 'linear-gradient(135deg, var(--success) 0%, #059669 100%)';

            setTimeout(() => { window.location.href = '/profile-setup'; }, 2000);
        }

        async function checkEmailVerified() {
            if (verificationStatus === 'verified') {
                window.location.href = '/profile-setup';
                return;
            }
            await loadStatus();
            if (verificationStatus !== 'verified') {
                alert('⏳ Email not verified yet. Please check your inbox and click the verification link.');
            }
        }

        async function resendVerificationEmail() {
            const btn = document.getElementById('resendBtn');
            const timer = document.getElementById('resendTimer');

            btn.disabled = true;
            resendCooldown = 60;

            try {
                const res = await fetch('/api/auth/resend-verification', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
                });
                const json = await res.json();
                alert(json.success ? '✓ ' + json.message : '⚠ ' + (json.message || 'Could not resend email.'));
            } catch (e) {
                alert('⚠ Could not reach the server. Try again.');
            }

            const countdown = setInterval(() => {
                resendCooldown--;
                timer.textContent = `(${resendCooldown}s)`;
                if (resendCooldown <= 0) {
                    btn.disabled = false;
                    timer.textContent = '';
                    clearInterval(countdown);
                }
            }, 1000);
        }
    </script>
</body>
</html>
