<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Security Verification — Sohni</title>
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
            --shadow-md: 0 4px 12px rgba(15, 23, 42, .05), 0 12px 32px rgba(15, 23, 42, .06);
            --shadow-neon: 0 8px 24px rgba(0, 132, 255, .18);
            --r-lg: 20px;
            --r-md: 14px;
        }

        html, body { height: 100%; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(135deg, var(--neon-soft), #f4fbff);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            width: 100%;
            max-width: 420px;
        }

        .logo {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo img {
            height: 40px;
            width: auto;
            margin-bottom: 12px;
        }

        .logo h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(120deg, var(--neon), var(--neon-2));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--r-lg);
            box-shadow: var(--shadow-md);
            padding: 40px 32px;
            text-align: center;
        }

        .card-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 20px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--neon), var(--neon-2));
            display: grid;
            place-content: center;
            font-size: 24px;
            color: #fff;
            box-shadow: var(--shadow-neon);
        }

        .card h2 {
            font-size: 22px;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 8px;
        }

        .card p {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 32px;
            line-height: 1.5;
        }

        .tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 28px;
            border-bottom: 1px solid var(--line);
            padding-bottom: 12px;
        }

        .tab-btn {
            flex: 1;
            padding: 10px;
            border: none;
            background: transparent;
            color: var(--muted);
            font-family: 'Sora', sans-serif;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all .3s ease;
        }

        .tab-btn:hover {
            color: var(--neon);
        }

        .tab-btn.active {
            color: var(--neon);
            border-bottom-color: var(--neon);
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
            animation: fadeIn .3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: none; }
        }

        /* Pattern Lock */
        .pattern-lock {
            margin: 20px 0;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        .pattern-dot {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--tint);
            border: 2px solid var(--line);
            cursor: pointer;
            display: grid;
            place-content: center;
            font-size: 20px;
            color: var(--muted);
            transition: all .2s ease;
            margin: 0 auto;
        }

        .pattern-dot:hover {
            border-color: var(--neon);
            background: linear-gradient(135deg, var(--neon-soft), #f4fbff);
        }

        .pattern-dot.active {
            background: linear-gradient(135deg, var(--neon), var(--neon-2));
            border-color: var(--neon);
            color: #fff;
            transform: scale(0.85);
            box-shadow: var(--shadow-neon);
        }

        .pattern-display {
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 20px;
        }

        .pattern-circle {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--tint);
            border: 1px solid var(--line);
            transition: all .2s ease;
        }

        .pattern-circle.filled {
            background: var(--neon);
            border-color: var(--neon);
            box-shadow: 0 0 8px rgba(0, 132, 255, .4);
        }

        /* PIN Input */
        .pin-input {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 20px;
        }

        .pin-digit {
            width: 50px;
            height: 50px;
            border: 2px solid var(--line);
            border-radius: var(--r-md);
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            color: var(--ink);
            background: var(--tint);
            transition: all .2s ease;
            font-family: 'Space Grotesk', sans-serif;
        }

        .pin-digit:focus {
            outline: none;
            border-color: var(--neon);
            box-shadow: 0 0 0 3px rgba(0, 132, 255, .1);
            background: #fff;
        }

        .pin-digit.filled {
            border-color: var(--neon);
            background: linear-gradient(135deg, var(--neon-soft), #f4fbff);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px 20px;
            border: 1px solid var(--line);
            border-radius: var(--r-md);
            background: var(--surface);
            color: var(--text);
            font-family: 'Sora', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all .22s ease;
            text-decoration: none;
        }

        .btn:hover {
            border-color: var(--neon);
            color: var(--neon);
            background: var(--tint);
        }

        .btn.neon {
            background: linear-gradient(120deg, var(--neon), var(--neon-2));
            color: #fff;
            border-color: transparent;
            box-shadow: var(--shadow-neon);
        }

        .btn.neon:hover {
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(0, 132, 255, .32);
        }

        .btn.neon:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            padding: 12px;
            border-radius: var(--r-md);
            font-size: 13px;
            margin-bottom: 16px;
            border-left: 4px solid;
        }

        .alert.danger {
            background: #fef2f2;
            border-left-color: var(--danger);
            color: #7f1d1d;
        }

        .alert.success {
            background: #f0fdf4;
            border-left-color: var(--success);
            color: #166534;
        }

        .back-link {
            margin-top: 24px;
            text-align: center;
        }

        .back-link a {
            color: var(--neon);
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: color .2s ease;
        }

        .back-link a:hover {
            color: var(--neon-2);
        }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, .3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 480px) {
            .card {
                padding: 32px 20px;
            }

            .pattern-dot {
                width: 50px;
                height: 50px;
            }

            .pin-digit {
                width: 40px;
                height: 40px;
                font-size: 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="logo">
        <img src="/images/app_logo.png" alt="Sohni">
        <h1>Sohni</h1>
    </div>

    <div class="card">
        <div class="card-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
        <h2>Security Verification</h2>
        <p>Please verify your identity to continue</p>

        <div id="verificationAlert"></div>

        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('pin')">
                <i class="fas fa-lock"></i> PIN
            </button>
            <button class="tab-btn" onclick="switchTab('pattern')">
                <i class="fas fa-grip"></i> Pattern
            </button>
        </div>

        <!-- PIN Verification -->
        <div class="tab-pane active" id="pane-pin">
            <form onsubmit="verifyPin(event)">
                <p style="font-size: 12px; color: var(--muted); margin-bottom: 16px;">Enter your 4-digit security PIN</p>
                
                <div class="pin-input">
                    <input type="text" class="pin-digit" id="pin-1" maxlength="1" inputmode="numeric" autofocus>
                    <input type="text" class="pin-digit" id="pin-2" maxlength="1" inputmode="numeric">
                    <input type="text" class="pin-digit" id="pin-3" maxlength="1" inputmode="numeric">
                    <input type="text" class="pin-digit" id="pin-4" maxlength="1" inputmode="numeric">
                </div>

                <button type="submit" class="btn neon">
                    <span id="verifyPinBtn"><i class="fas fa-check"></i> Verify PIN</span>
                </button>
            </form>
        </div>

        <!-- Pattern Verification -->
        <div class="tab-pane" id="pane-pattern">
            <form onsubmit="verifyPattern(event)">
                <p style="font-size: 12px; color: var(--muted); margin-bottom: 16px;">Draw your security pattern</p>
                
                <div class="pattern-display">
                    <div class="pattern-circle" id="dot-1"></div>
                    <div class="pattern-circle" id="dot-2"></div>
                    <div class="pattern-circle" id="dot-3"></div>
                </div>

                <div class="pattern-lock" id="patternGrid"></div>

                <button type="submit" class="btn neon">
                    <span id="verifyPatternBtn"><i class="fas fa-check"></i> Verify Pattern</span>
                </button>

                <div style="margin-top: 12px;">
                    <button type="button" class="btn" onclick="resetPattern()" style="background: var(--tint); color: var(--muted);">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </form>
        </div>

        <div class="back-link">
            <a href="/dashboard"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>
</div>

<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    let patternSequence = [];

    // PIN Input Auto-focus
    document.querySelectorAll('.pin-digit').forEach((input, index) => {
        input.addEventListener('input', (e) => {
            if (e.target.value && index < 3) {
                document.querySelectorAll('.pin-digit')[index + 1].focus();
            }
            updatePinDisplay();
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                document.querySelectorAll('.pin-digit')[index - 1].focus();
            }
        });
    });

    function updatePinDisplay() {
        document.querySelectorAll('.pin-digit').forEach(input => {
            if (input.value) {
                input.classList.add('filled');
            } else {
                input.classList.remove('filled');
            }
        });
    }

    // Pattern Lock
    function initializePattern() {
        const grid = document.getElementById('patternGrid');
        grid.innerHTML = '';
        patternSequence = [];

        for (let i = 1; i <= 9; i++) {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'pattern-dot';
            dot.textContent = i;
            dot.onclick = (e) => {
                e.preventDefault();
                selectPatternDot(i, dot);
            };
            grid.appendChild(dot);
        }
    }

    function selectPatternDot(number, element) {
        if (!patternSequence.includes(number)) {
            patternSequence.push(number);
            element.classList.add('active');
            updatePatternDisplay();
        }
    }

    function updatePatternDisplay() {
        document.querySelectorAll('.pattern-circle').forEach((circle, index) => {
            if (index < patternSequence.length) {
                circle.classList.add('filled');
            } else {
                circle.classList.remove('filled');
            }
        });
    }

    function resetPattern() {
        patternSequence = [];
        document.querySelectorAll('.pattern-dot').forEach(dot => dot.classList.remove('active'));
        updatePatternDisplay();
    }

    function switchTab(tab) {
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('pane-' + tab).classList.add('active');
        event.target.closest('.tab-btn').classList.add('active');

        if (tab === 'pattern') {
            initializePattern();
        }
    }

    async function verifyPin(event) {
        event.preventDefault();

        const pins = Array.from(document.querySelectorAll('.pin-digit')).map(i => i.value);
        const pin = pins.join('');

        if (pin.length !== 4) {
            showAlert('Please enter a 4-digit PIN', 'danger');
            return;
        }

        try {
            const btn = document.getElementById('verifyPinBtn');
            btn.innerHTML = '<span class="spinner"></span> Verifying...';
            btn.parentElement.disabled = true;

            const res = await fetch('/api/settings/security/pin/verify', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({ pin })
            });

            const json = await res.json();

            if (!json.success) {
                showAlert(json.message || 'Invalid PIN', 'danger');
                document.querySelectorAll('.pin-digit').forEach(i => i.value = '');
                updatePinDisplay();
            } else {
                showAlert('PIN verified successfully!', 'success');
                setTimeout(() => {
                    const redirect = new URLSearchParams(window.location.search).get('redirect_to') || '/dashboard';
                    window.location.href = redirect;
                }, 1000);
            }

            btn.innerHTML = '<i class="fas fa-check"></i> Verify PIN';
            btn.parentElement.disabled = false;
        } catch (e) {
            console.error(e);
            showAlert('Error verifying PIN', 'danger');
        }
    }

    async function verifyPattern(event) {
        event.preventDefault();

        if (patternSequence.length < 4) {
            showAlert('Pattern must have at least 4 dots', 'danger');
            return;
        }

        const pattern = patternSequence.join('-');

        try {
            const btn = document.getElementById('verifyPatternBtn');
            btn.innerHTML = '<span class="spinner"></span> Verifying...';
            btn.parentElement.disabled = true;

            const res = await fetch('/api/settings/security/pattern/verify', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({ pattern })
            });

            const json = await res.json();

            if (!json.success) {
                showAlert(json.message || 'Invalid pattern', 'danger');
                resetPattern();
            } else {
                showAlert('Pattern verified successfully!', 'success');
                setTimeout(() => {
                    const redirect = new URLSearchParams(window.location.search).get('redirect_to') || '/dashboard';
                    window.location.href = redirect;
                }, 1000);
            }

            btn.innerHTML = '<i class="fas fa-check"></i> Verify Pattern';
            btn.parentElement.disabled = false;
        } catch (e) {
            console.error(e);
            showAlert('Error verifying pattern', 'danger');
        }
    }

    function showAlert(message, type = 'success') {
        const alert = document.getElementById('verificationAlert');
        alert.innerHTML = `
            <div class="alert ${type}">
                <i class="fas fa-${type === 'danger' ? 'exclamation-circle' : 'check-circle'}"></i>
                ${message}
            </div>
        `;
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', initializePattern);
</script>

</body>
</html>
