<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sohni — Sign In & Sign Up</title>
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
            --error: #ef4444;
            --success: #10b981;
            --radius: 20px;
            --ease: cubic-bezier(0.22, 1, 0.36, 1);
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            background:
                radial-gradient(circle at 75% 15%, rgba(0, 194, 255, .16), transparent 28%),
                radial-gradient(circle at 15% 85%, rgba(0, 132, 255, .1), transparent 32%),
                linear-gradient(135deg, var(--white) 0%, var(--bg-soft) 100%);
            color: var(--ink);
            min-height: 100vh;
            overflow-x: hidden;
        }

        h1, h2, .logo-text { font-family: 'Sora', sans-serif; }

        .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
            align-items: stretch;
        }

        /* ============ Left Panel ============ */
        .panel-left {
            background: linear-gradient(135deg, #06121f 0%, #0a2540 55%, #063a5c 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: clamp(30px, 5vw, 60px);
            position: relative;
            overflow: hidden;
            border-right: 1px solid rgba(255, 255, 255, .08);
        }

        .panel-left::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(0, 194, 255, 0.15), transparent 70%);
            top: -200px;
            left: -200px;
            pointer-events: none;
        }

        .panel-left::after {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(0, 132, 255, 0.1), transparent 70%);
            bottom: -150px;
            right: -150px;
            pointer-events: none;
        }

        .panel-left .grid-glow {
            position: absolute;
            inset: 0;
            opacity: .22;
            background-image: linear-gradient(rgba(255,255,255,.08) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.08) 1px, transparent 1px);
            background-size: 42px 42px;
            mask-image: linear-gradient(to bottom right, black, transparent 72%);
            pointer-events: none;
        }

        .floating-orb {
            position: absolute;
            width: 18px;
            height: 18px;
            border: 1px solid rgba(160, 239, 255, .7);
            border-radius: 50%;
            box-shadow: 0 0 22px rgba(0, 194, 255, .7);
            animation: drift 7s ease-in-out infinite;
            pointer-events: none;
        }

        .floating-orb.one { top: 22%; right: 14%; }
        .floating-orb.two { bottom: 24%; left: 12%; width: 10px; height: 10px; animation-delay: -2s; }

        @keyframes drift {
            0%, 100% { transform: translate3d(0, 0, 0); opacity: .45; }
            50% { transform: translate3d(18px, -24px, 0); opacity: 1; }
        }

        .brand-dark {
            display: flex;
            align-items: center;
            gap: 14px;
            z-index: 2;
        }

        .brand-dark img {
            width: 48px;
            height: 48px;
            filter: drop-shadow(0 0 18px rgba(0, 194, 255, 0.6));
        }

        .brand-dark .logo-text {
            font-size: 28px;
            font-weight: 800;
            color: #fff;
            background: linear-gradient(120deg, #fff, #b3e9ff);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .left-content {
            z-index: 2;
            max-width: 540px;
            animation: panelReveal .8s var(--ease) both;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            padding: 8px 12px;
            border: 1px solid rgba(146, 231, 255, .25);
            border-radius: 999px;
            background: rgba(255, 255, 255, .07);
            color: #a9edff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }

        .eyebrow::before {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #55e6ff;
            box-shadow: 0 0 12px #55e6ff;
            animation: livePulse 1.8s ease-in-out infinite;
        }

        .left-content h2 {
            font-size: clamp(28px, 4vw, 44px);
            color: #fff;
            line-height: 1.2;
            margin-bottom: 20px;
            font-weight: 800;
            letter-spacing: -.8px;
        }

        .left-content h2 span {
            display: block;
            color: #8feaff;
            background: linear-gradient(120deg, #fff, #75e5ff);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .left-content p {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.75);
            line-height: 1.8;
            max-width: 420px;
            font-weight: 300;
            margin-bottom: 32px;
        }

        .features-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .feature-left {
            display: flex;
            gap: 14px;
            align-items: flex-start;
            padding: 13px 15px;
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 16px;
            background: rgba(255, 255, 255, .055);
            transition: transform .3s var(--ease), background .3s ease, border-color .3s ease;
            animation: featureReveal .7s var(--ease) both;
        }

        .feature-left:nth-child(2) { animation-delay: .12s; }
        .feature-left:nth-child(3) { animation-delay: .24s; }
        .feature-left:hover {
            transform: translateX(8px);
            background: rgba(255, 255, 255, .1);
            border-color: rgba(143, 234, 255, .3);
        }

        .feature-left .check {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00c2ff, #0084ff);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .feature-left h4 {
            font-size: 15px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 4px;
        }

        .feature-left p {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.65);
            margin: 0;
            font-weight: 300;
        }

        .footer-dark {
            z-index: 2;
            animation: panelReveal .8s .35s var(--ease) both;
        }

        .footer-dark p {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
            margin: 0;
        }

        @keyframes panelReveal {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes featureReveal {
            from { opacity: 0; transform: translateX(-18px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes livePulse {
            0%, 100% { transform: scale(1); opacity: .7; }
            50% { transform: scale(1.35); opacity: 1; }
        }

        /* ============ Right Panel ============ */
        .panel-right {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: clamp(30px, 5vw, 60px);
            background: var(--white);
            position: relative;
            overflow: hidden;
            isolation: isolate;
        }

        .panel-right::before {
            content: '';
            position: absolute;
            width: 450px;
            height: 450px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(0, 194, 255, 0.08), transparent 70%);
            top: -150px;
            right: -150px;
        }

        .form-wrapper {
            width: 100%;
            max-width: 420px;
            z-index: 2;
            padding: 36px;
            border: 1px solid rgba(255, 255, 255, .9);
            border-radius: 28px;
            background: rgba(255, 255, 255, .78);
            box-shadow: 0 24px 70px rgba(8, 45, 82, .12), 0 0 0 8px rgba(255, 255, 255, .34);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .form-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .form-header::before {
            content: 'S';
            display: grid;
            place-items: center;
            width: 48px;
            height: 48px;
            margin: 0 auto 18px;
            border-radius: 15px;
            background: linear-gradient(135deg, var(--neon), var(--neon-deep));
            color: #fff;
            font-family: 'Sora', sans-serif;
            font-size: 24px;
            font-weight: 800;
            box-shadow: 0 10px 24px var(--neon-glow);
        }

        .form-header h1 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 10px;
            color: var(--ink);
        }

        .form-header p {
            font-size: 14px;
            color: var(--ink-dim);
            font-weight: 300;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"],
        input[type="tel"] {
            width: 100%;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1.5px solid rgba(11, 21, 38, 0.1);
            background: rgba(244, 249, 255, 0.6);
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            color: var(--ink);
            transition: border-color 0.3s, box-shadow 0.3s;
            box-shadow: 0 2px 8px rgba(8, 45, 82, .03);
        }

        input:focus {
            outline: none;
            border-color: var(--neon);
            box-shadow: 0 0 0 3px rgba(0, 194, 255, 0.1);
            background: var(--white);
        }

        input::placeholder {
            color: var(--ink-faint);
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
            transition: transform 0.3s var(--ease), box-shadow 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--neon), var(--neon-deep));
            color: #fff;
            box-shadow: 0 8px 24px var(--neon-glow);
            position: relative;
            overflow: hidden;
        }

        .btn-primary::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(110deg, transparent 25%, rgba(255, 255, 255, .28), transparent 70%);
            transform: translateX(-120%);
            transition: transform .6s ease;
        }

        .btn-primary:hover::after { transform: translateX(120%); }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px var(--neon-glow);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .toggle-auth {
            text-align: center;
            margin-top: 26px;
            font-size: 14px;
            color: var(--ink-dim);
        }

        .toggle-auth a {
            color: var(--neon-deep);
            text-decoration: none;
            font-weight: 700;
            cursor: pointer;
            transition: color 0.3s;
        }

        .toggle-auth a:hover {
            color: var(--neon);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: var(--ink-dim);
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--neon);
        }

        /* Forms visibility */
        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
            animation: fadeIn 0.4s var(--ease);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ============ Responsive ============ */
        @media (max-width: 1024px) {
            .container {
                grid-template-columns: 1fr;
            }

            .panel-left {
                display: none;
            }

            .panel-right {
                min-height: 100vh;
                justify-content: center;
            }

            .form-wrapper {
                max-width: 100%;
            }
        }

        @media (max-width: 520px) {
            .panel-right {
                padding: 30px 20px;
            }

            .form-wrapper {
                padding: 28px 20px;
                border-radius: 22px;
                box-shadow: 0 18px 45px rgba(8, 45, 82, .1);
            }

            .form-header h1 {
                font-size: 24px;
            }

            .form-header p {
                font-size: 13px;
            }

            input[type="email"],
            input[type="password"],
            input[type="text"],
            input[type="tel"] {
                padding: 12px 14px;
                font-size: 16px;
            }

            .btn {
                padding: 12px 20px;
                font-size: 14px;
            }
        }

        .error-msg {
            color: var(--error);
            font-size: 12px;
            margin-top: 6px;
            display: none;
        }

        .error-msg.show {
            display: block;
            animation: errorReveal .2s ease both;
        }

        input.error {
            border-color: var(--error) !important;
            background: rgba(239, 68, 68, 0.05) !important;
        }

        input.valid {
            border-color: rgba(16, 185, 129, .7);
            background: rgba(16, 185, 129, .04);
        }

        .field-hint {
            display: block;
            margin-top: 7px;
            color: var(--ink-faint);
            font-size: 11px;
            line-height: 1.5;
        }

        .password-strength {
            height: 4px;
            margin-top: 9px;
            overflow: hidden;
            border-radius: 99px;
            background: #e8eef5;
        }

        .password-strength span {
            display: block;
            width: 0;
            height: 100%;
            border-radius: inherit;
            transition: width .25s ease, background .25s ease;
        }

        .password-strength-text {
            display: block;
            margin-top: 5px;
            color: var(--ink-faint);
            font-size: 11px;
        }

        @keyframes errorReveal {
            from { opacity: 0; transform: translateY(-3px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Left Panel -->
        <div class="panel-left">
            <div class="grid-glow"></div>
            <span class="floating-orb one"></span>
            <span class="floating-orb two"></span>
            <div class="brand-dark">
                <img src="/images/app_logo.png" alt="Sohni">
                <span class="logo-text">Sohni</span>
            </div>

            <div class="left-content">
                <div class="eyebrow">Your space to connect</div>
                <h2>Real conversations.<span>Real community.</span></h2>
                <p>Chat faster, share freely, and stay close to the people who matter. Sohni brings your community together in one secure space.</p>

                <div class="features-list">
                    <div class="feature-left">
                        <div class="check">✓</div>
                        <div>
                            <h4>End-to-End Encrypted</h4>
                            <p>Your messages are yours alone</p>
                        </div>
                    </div>
                    <div class="feature-left">
                        <div class="check">✓</div>
                        <div>
                            <h4>Lightning Fast</h4>
                            <p>Optimized for all network speeds</p>
                        </div>
                    </div>
                    <div class="feature-left">
                        <div class="check">✓</div>
                        <div>
                            <h4>Local Groups</h4>
                            <p>Connect by city and interests</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-dark">
                <p>© 2026 Sohni. Made with ❤️ for Pakistan 🇵🇰</p>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="panel-right">
            <div class="form-wrapper">
                <!-- Sign In Form -->
                <div class="form-section active" id="signInForm">
                    <div class="form-header">
                        <h1>Welcome Back</h1>
                        <p>Sign in to continue your conversations</p>
                    </div>

                    <form id="signInFrm" onsubmit="handleSignIn(event)">
                        <div class="form-group">
                            <label for="signInEmail">Email Address</label>
                                    <input type="email" id="signInEmail" name="email" placeholder="you@example.com" autocomplete="email" required>
                            <div class="error-msg" id="signInEmailErr"></div>
                        </div>

                        <div class="form-group">
                            <label for="signInPass">Password</label>
                            <input type="password" id="signInPass" name="password" placeholder="••••••••" autocomplete="current-password" minlength="6" required>
                            <div class="error-msg" id="signInPassErr"></div>
                        </div>

                        <div class="checkbox-group" style="margin-bottom: 28px;">
                            <input type="checkbox" id="rememberMe" name="remember">
                            <label for="rememberMe" style="margin: 0; text-transform: none; text-indent: 0;">Remember me</label>
                        </div>

                        <button type="submit" class="btn btn-primary">Sign In</button>
                    </form>

                    <div class="toggle-auth">
                        Don't have an account? <a onclick="toggleForm('signUp')">Sign up</a>
                    </div>
                </div>

                <!-- Sign Up Form -->
                <div class="form-section" id="signUpForm">
                    <div class="form-header">
                        <h1>Create Account</h1>
                        <p>Join thousands of Pakistanis on Sohni</p>
                    </div>

                    <div id="signupSuccess" style="display:none; background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.35); color: #047857; border-radius: 12px; padding: 16px 18px; font-size: 14px; margin-bottom: 20px; line-height: 1.7;">
                        ✅ <strong>Account created!</strong><br>
                        📧 We've sent an activation email. Open it and click <strong>“Activate My Account”</strong>.<br>
                        <span style="color:#b45309;">⚠️ Not in your inbox? <strong>Check the Spam folder</strong> — and mark it “Not Spam”.</span><br>
                        <span style="font-size:12px; color:#059669;">⏳ Waiting for verification... this page will continue automatically once you activate.</span>
                    </div>

                    <form id="signUpFrm" onsubmit="handleSignUp(event)">
                        <div class="form-group">
                            <label for="signUpEmail">Email Address</label>
                            <input type="email" id="signUpEmail" name="email" placeholder="you@example.com" autocomplete="email" required>
                            <div class="error-msg" id="signUpEmailErr"></div>
                        </div>

                        <div class="form-group">
                            <label for="signUpPass">Password</label>
                            <input type="password" id="signUpPass" name="password" placeholder="Create a strong password" autocomplete="new-password" minlength="8" required>
                            <div class="error-msg" id="signUpPassErr"></div>
                            <small class="field-hint">Use 8+ characters with uppercase, lowercase, and a number.</small>
                            <div class="password-strength" aria-hidden="true"><span id="passwordStrengthBar"></span></div>
                            <small class="password-strength-text" id="passwordStrengthText">Password strength: —</small>
                        </div>

                        <div class="form-group">
                            <label for="signUpConfirm">Confirm Password</label>
                            <input type="password" id="signUpConfirm" name="confirm" placeholder="Repeat your password" autocomplete="new-password" required>
                            <div class="error-msg" id="signUpConfirmErr"></div>
                        </div>

                        <div class="checkbox-group" style="margin-bottom: 28px;">
                            <input type="checkbox" id="agreeTerms" name="agree" required>
                            <label for="agreeTerms" style="margin: 0; text-transform: none; text-indent: 0;">I agree to Terms & Privacy Policy</label>
                            <div class="error-msg" id="signUpTermsErr" style="margin-top: 0;"></div>
                        </div>

                        <button type="submit" class="btn btn-primary">Create Account</button>
                    </form>

                    <div class="toggle-auth">
                        Already have an account? <a onclick="toggleForm('signIn')">Sign in</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleForm(type) {
            document.getElementById('signInForm').classList.remove('active');
            document.getElementById('signUpForm').classList.remove('active');
            
            if (type === 'signIn') {
                document.getElementById('signInForm').classList.add('active');
            } else {
                document.getElementById('signUpForm').classList.add('active');
            }
        }

        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/i;
            return re.test(email);
        }

        function validatePassword(pass) {
            return pass.length >= 8 && /[A-Z]/.test(pass) && /[a-z]/.test(pass) && /\d/.test(pass);
        }

        function validateLoginPassword(pass) {
            return pass.length >= 6;
        }

        function showError(elementId, message) {
            const elem = document.getElementById(elementId);
            elem.textContent = message;
            elem.classList.add('show');
            const input = elem.parentElement.querySelector('input');
            if (input) {
                input.classList.add('error');
                input.classList.remove('valid');
                input.setAttribute('aria-invalid', 'true');
            }
        }

        function clearError(elementId) {
            const elem = document.getElementById(elementId);
            elem.classList.remove('show');
            const input = elem.parentElement.querySelector('input');
            if (input) {
                input.classList.remove('error');
                input.removeAttribute('aria-invalid');
            }
        }

        function markValid(input) {
            input.classList.remove('error');
            input.classList.add('valid');
            input.removeAttribute('aria-invalid');
        }

        function updatePasswordStrength(password) {
            const bar = document.getElementById('passwordStrengthBar');
            const text = document.getElementById('passwordStrengthText');
            if (!bar || !text) return;

            let score = 0;
            if (password.length >= 8) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[a-z]/.test(password)) score++;
            if (/\d/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;

            const levels = [
                { width: '0%', color: '#e8eef5', label: 'Password strength: —' },
                { width: '25%', color: '#ef4444', label: 'Password strength: weak' },
                { width: '50%', color: '#f59e0b', label: 'Password strength: fair' },
                { width: '75%', color: '#22c55e', label: 'Password strength: good' },
                { width: '100%', color: '#10b981', label: 'Password strength: strong' },
            ];
            const level = levels[Math.min(score, 4)];
            bar.style.width = level.width;
            bar.style.background = level.color;
            text.textContent = level.label;
        }

        const CSRF = document.querySelector('meta[name="csrf-token"]').content;

        document.getElementById('signUpPass').addEventListener('input', event => {
            updatePasswordStrength(event.target.value);
            if (validatePassword(event.target.value)) {
                clearError('signUpPassErr');
                markValid(event.target);
            }
        });

        document.getElementById('signUpConfirm').addEventListener('input', event => {
            const password = document.getElementById('signUpPass').value;
            if (event.target.value && event.target.value === password) {
                clearError('signUpConfirmErr');
                markValid(event.target);
            }
        });

        async function apiPost(url, body) {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify(body)
            });
            const data = await res.json().catch(() => ({}));
            return { ok: res.ok, status: res.status, data };
        }

        async function handleSignIn(e) {
            e.preventDefault();
            clearError('signInEmailErr');
            clearError('signInPassErr');

            const email = document.getElementById('signInEmail').value.trim().toLowerCase();
            const password = document.getElementById('signInPass').value;
            let hasError = false;

            if (!validateEmail(email)) {
                showError('signInEmailErr', 'Please enter a valid email address');
                hasError = true;
            }

            if (!validateLoginPassword(password)) {
                showError('signInPassErr', 'Password must be at least 6 characters');
                hasError = true;
            }

            if (hasError) return;

            const btn = e.target.querySelector('button[type="submit"]');
            if (btn) { btn.disabled = true; btn.textContent = 'Signing in...'; }

            let result;
            try {
                result = await apiPost('/api/auth/signin', { email, password });
            } catch (error) {
                showError('signInPassErr', 'Unable to connect. Please try again.');
                if (btn) { btn.disabled = false; btn.textContent = 'Sign In'; }
                return;
            }
            const { ok, data } = result;

            if (ok && data.success) {
                localStorage.setItem('signupEmail', email);
                window.location.href = data.data.redirect || '/dashboard';
            } else {
                showError('signInPassErr', data.message || 'Invalid email or password');
                if (btn) { btn.disabled = false; btn.textContent = 'Sign In'; }
            }
        }

        async function handleSignUp(e) {
            e.preventDefault();
            clearError('signUpEmailErr');
            clearError('signUpPassErr');
            clearError('signUpConfirmErr');
            clearError('signUpTermsErr');

            const email = document.getElementById('signUpEmail').value.trim().toLowerCase();
            const password = document.getElementById('signUpPass').value;
            const confirm = document.getElementById('signUpConfirm').value;
            let hasError = false;

            if (!validateEmail(email)) {
                showError('signUpEmailErr', 'Please enter a valid email address');
                hasError = true;
            }

            if (!validatePassword(password)) {
                showError('signUpPassErr', 'Use 8+ characters with uppercase, lowercase, and a number');
                hasError = true;
            }

            if (password !== confirm) {
                showError('signUpConfirmErr', 'Passwords do not match');
                hasError = true;
            }

            if (!document.getElementById('agreeTerms').checked) {
                showError('signUpTermsErr', 'Please agree to the Terms & Privacy Policy');
                hasError = true;
            }

            if (hasError) return;

            const btn = e.target.querySelector('button[type="submit"]');
            if (btn) { btn.disabled = true; btn.textContent = 'Creating account...'; }

            let result;
            try {
                result = await apiPost('/api/auth/signup', { email, password, password_confirmation: confirm });
            } catch (error) {
                showError('signUpEmailErr', 'Unable to connect. Please try again.');
                if (btn) { btn.disabled = false; btn.textContent = 'Create Account'; }
                return;
            }
            const { ok, status, data } = result;

            if (ok && data.success) {
                localStorage.setItem('signupEmail', email);
                // Stay here — show the message and auto-continue once the email is activated
                document.getElementById('signUpFrm').style.display = 'none';
                document.getElementById('signupSuccess').style.display = 'block';
                pollVerification();
            } else {
                if (status === 422 && data.errors) {
                    if (data.errors.email) showError('signUpEmailErr', data.errors.email[0]);
                    if (data.errors.password) showError('signUpPassErr', data.errors.password[0]);
                } else {
                    showError('signUpEmailErr', data.message || 'Signup failed. Please try again.');
                }
                if (btn) { btn.disabled = false; btn.textContent = 'Create Account'; }
            }
        }

        // After signup: check every 4s whether the email was activated
        function pollVerification() {
            const timer = setInterval(async () => {
                try {
                    const res = await fetch('/api/auth/verification-status', { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) return;
                    const json = await res.json();
                    if (json.success && json.data.verified) {
                        clearInterval(timer);
                        window.location.href = json.data.profile_complete ? '/dashboard' : '/profile-setup';
                    }
                } catch (e) { /* retry */ }
            }, 4000);
        }

    </script>
</body>
</html>
