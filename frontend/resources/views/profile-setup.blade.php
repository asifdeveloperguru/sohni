<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Complete Your Profile — Sohni</title>
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
            background: linear-gradient(135deg, var(--white) 0%, var(--bg-soft) 100%);
            color: var(--ink);
            min-height: 100vh;
            padding: 20px;
        }

        h1, h2, .logo-text { font-family: 'Sora', sans-serif; }

        .container {
            max-width: 600px;
            margin: 0 auto;
        }

        header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 40px;
            padding: 20px 0;
            border-bottom: 1px solid rgba(11, 21, 38, 0.1);
        }

        header img {
            width: 44px;
            height: 44px;
            filter: drop-shadow(0 4px 14px rgba(0, 194, 255, 0.35));
        }

        .logo-text {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(120deg, #0b1526 20%, var(--neon-deep) 60%, var(--neon));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .progress-section {
            margin-bottom: 40px;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background: rgba(11, 21, 38, 0.1);
            border-radius: 100px;
            overflow: hidden;
            margin-bottom: 12px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--neon), var(--neon-deep));
            border-radius: 100px;
            transition: width 0.4s var(--ease);
            width: 20%;
        }

        .progress-text {
            font-size: 12px;
            color: var(--ink-faint);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .step-title {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 12px;
            color: var(--ink);
        }

        .step-desc {
            font-size: 14px;
            color: var(--ink-dim);
            margin-bottom: 32px;
            line-height: 1.6;
        }

        .profile-card {
            background: var(--white);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(11, 21, 38, 0.08);
            margin-bottom: 30px;
        }

        .form-step {
            display: none;
        }

        .form-step.active {
            display: block;
            animation: slideIn 0.4s var(--ease);
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .form-group {
            margin-bottom: 22px;
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

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="file"],
        select,
        textarea {
            width: 100%;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1.5px solid rgba(11, 21, 38, 0.1);
            background: rgba(244, 249, 255, 0.6);
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            color: var(--ink);
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--neon);
            box-shadow: 0 0 0 3px rgba(0, 194, 255, 0.1);
            background: var(--white);
        }

        input::placeholder,
        textarea::placeholder {
            color: var(--ink-faint);
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-row .form-group {
            margin-bottom: 0;
        }

        .profile-pic-upload {
            text-align: center;
            padding: 40px 24px;
            border: 2px dashed rgba(0, 194, 255, 0.3);
            border-radius: 16px;
            background: rgba(0, 194, 255, 0.05);
            transition: all 0.3s;
            cursor: pointer;
        }

        .profile-pic-upload:hover {
            border-color: var(--neon);
            background: rgba(0, 194, 255, 0.1);
        }

        .profile-pic-upload input[type="file"] {
            display: none;
        }

        .pic-placeholder {
            font-size: 48px;
            margin-bottom: 12px;
        }

        .pic-text {
            font-size: 13px;
            color: var(--ink-dim);
            font-weight: 500;
            margin-bottom: 4px;
        }

        .pic-subtext {
            font-size: 12px;
            color: var(--ink-faint);
        }

        .pic-preview {
            margin-top: 20px;
        }

        .pic-preview img {
            width: 120px;
            height: 120px;
            border-radius: 16px;
            object-fit: cover;
            box-shadow: 0 8px 20px rgba(0, 194, 255, 0.2);
        }

        .sohni-id-selection {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            margin-bottom: 20px;
        }

        .id-option {
            padding: 14px;
            border: 2px solid rgba(11, 21, 38, 0.1);
            border-radius: 12px;
            background: var(--white);
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .id-option:hover {
            border-color: var(--neon);
            background: rgba(0, 194, 255, 0.05);
        }

        .id-option input[type="radio"] {
            display: none;
        }

        .id-option input[type="radio"]:checked + .id-info {
            color: var(--neon-deep);
        }

        .id-option input:checked + .id-info {
            font-weight: 600;
        }

        .id-option.selected {
            border-color: var(--neon);
            background: rgba(0, 194, 255, 0.1);
        }

        .id-info {
            font-size: 13px;
            font-weight: 500;
        }

        .id-price {
            font-size: 11px;
            color: var(--ink-faint);
            margin-top: 4px;
        }

        .hint-text {
            font-size: 12px;
            color: var(--ink-faint);
            margin-top: 6px;
            font-weight: 300;
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

        .btn-group {
            display: flex;
            gap: 14px;
            margin-top: 40px;
        }

        .btn {
            flex: 1;
            padding: 14px 24px;
            border-radius: 12px;
            font-family: 'Sora', sans-serif;
            font-size: 15px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.3s var(--ease);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--neon), var(--neon-deep));
            color: #fff;
            box-shadow: 0 8px 24px var(--neon-glow);
        }

        .btn-primary:hover:not(:disabled) {
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
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .error-msg {
            color: var(--error);
            font-size: 12px;
            margin-top: 6px;
            display: none;
        }

        .error-msg.show {
            display: block;
        }

        input.error,
        select.error,
        textarea.error {
            border-color: var(--error) !important;
            background: rgba(239, 68, 68, 0.05) !important;
        }

        .optional-badge {
            display: inline-block;
            padding: 3px 8px;
            background: rgba(0, 194, 255, 0.15);
            border-radius: 6px;
            font-size: 11px;
            color: var(--neon-deep);
            font-weight: 600;
            margin-left: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .degree-card {
            background: rgba(244, 249, 255, 0.7);
            border: 1.5px solid rgba(0, 194, 255, 0.2);
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 14px;
            position: relative;
            animation: slideIn 0.3s var(--ease);
        }

        .degree-card .degree-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .degree-card .degree-num {
            font-size: 12px;
            font-weight: 700;
            color: var(--neon-deep);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .degree-remove {
            background: rgba(239, 68, 68, 0.1);
            border: none;
            color: var(--error);
            width: 26px;
            height: 26px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            line-height: 1;
        }

        .degree-remove:hover {
            background: rgba(239, 68, 68, 0.2);
        }

        .degree-card .form-row {
            margin-top: 12px;
        }

        @media (max-width: 640px) {
            .profile-card {
                padding: 24px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .sohni-id-selection {
                grid-template-columns: 1fr;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            .step-title {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <img src="/images/app_logo.png" alt="Sohni">
            <span class="logo-text">Sohni</span>
        </header>

        <div class="profile-card">
            <!-- Progress -->
            <div class="progress-section">
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <div class="progress-text" id="progressText">Step 1 of 5</div>
            </div>

            <!-- Step 1: Basic Info -->
            <div class="form-step active" id="step1">
                <h2 class="step-title">What's Your Name?</h2>
                <p class="step-desc">We'll use this to personalize your Sohni experience and help friends find you.</p>

                <form>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName">First Name</label>
                            <input type="text" id="firstName" name="firstName" placeholder="John" required>
                            <div class="error-msg"></div>
                        </div>
                        <div class="form-group">
                            <label for="lastName">Last Name</label>
                            <input type="text" id="lastName" name="lastName" placeholder="Doe" required>
                            <div class="error-msg"></div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Step 2: Phone & Email -->
            <div class="form-step" id="step2">
                <h2 class="step-title">Contact Information</h2>
                <p class="step-desc">Keep your contact info up to date so we can reach you when needed.</p>

                <form>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" placeholder="+92 300 1234567" required>
                        <div class="hint-text">Include country code (+92 for Pakistan)</div>
                        <div class="error-msg"></div>
                    </div>
                    <div class="form-group">
                        <label for="emailStep2">Email Address</label>
                        <input type="email" id="emailStep2" name="email" placeholder="you@gmail.com" readonly style="background: rgba(244, 249, 255, 0.3);">
                        <div class="hint-text">Verified during signup</div>
                    </div>
                </form>
            </div>

            <!-- Step 3: Sohni ID -->
            <div class="form-step" id="step3">
                <h2 class="step-title">Choose Your Sohni ID</h2>
                <p class="step-desc">Your unique identifier — pick an auto-generated one or upgrade for a custom premium ID.</p>

                <form>
                    <div class="form-group">
                        <label>ID Selection</label>
                        <div class="sohni-id-selection" id="idSelection">
                            <label class="id-option">
                                <input type="radio" name="sohniId" value="free" checked onchange="selectIdOption(this)">
                                <div class="id-info">📱 Auto-Generated</div>
                                <div class="id-price">Free — unique 14-digit ID</div>
                            </label>
                            <label class="id-option">
                                <input type="radio" name="sohniId" value="premium" onchange="selectIdOption(this)">
                                <div class="id-info">✨ Premium (14-digit)</div>
                                <div class="id-price">Rs. 2,999/year</div>
                            </label>
                        </div>
                    </div>

                    <div class="form-group" id="idPreview" style="display: none;">
                        <label>Your Sohni ID</label>
                        <input type="text" id="generatedId" readonly style="background: rgba(0, 194, 255, 0.1); border-color: var(--neon);" value="12345678901234">
                        <div class="hint-text">✓ This is your unique handle for the app</div>
                    </div>

                    <div class="form-group" id="premiumPrice" style="display: none; background: rgba(0, 194, 255, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(0, 194, 255, 0.2);">
                        <div style="font-weight: 600; color: var(--neon-deep); margin-bottom: 8px;">Premium Package Details</div>
                        <div style="font-size: 13px; color: var(--ink-dim); margin-bottom: 12px;">
                            ✓ Custom 14-digit Sohni ID<br>
                            ✓ Premium badge on profile<br>
                            ✓ Early access to new features<br>
                            ✓ Priority support
                        </div>
                        <button type="button" class="btn btn-primary" style="margin: 0; width: 100%; padding: 10px;" onclick="alert('Payment gateway integration coming soon!')">
                            Upgrade to Premium (Rs. 2,999)
                        </button>
                    </div>
                </form>
            </div>

            <!-- Step 4: Location & Education -->
            <div class="form-step" id="step4">
                <h2 class="step-title">More About You</h2>
                <p class="step-desc">Help us connect you with people in your area. Your details are encrypted — only you can see them. 🔒</p>

                <form>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" placeholder="e.g., Lahore, Punjab" required>
                        <div class="hint-text">City and province are enough</div>
                        <div class="error-msg"></div>
                    </div>

                    <div class="form-group">
                        <label for="aboutMe">About Me <span class="optional-badge">Optional</span></label>
                        <textarea id="aboutMe" name="about_me" maxlength="2000" rows="4" placeholder="Tell people about yourself, your skills, and interests..."></textarea>
                        <div class="hint-text">This appears on your profile and downloadable profile.</div>
                    </div>

                    <div class="form-group">
                        <label>Work Experience <span class="optional-badge">Optional</span></label>
                        <div class="hint-text" style="margin-bottom: 12px;">Add your recent roles and achievements.</div>
                        <div id="experienceList"></div>
                        <button type="button" class="btn btn-secondary" style="width: 100%; padding: 10px; font-size: 13px;" onclick="addExperience()">＋ Add Experience</button>
                    </div>

                    <div class="form-group">
                        <label>Education <span class="optional-badge">Optional</span></label>
                        <div class="hint-text" style="margin-bottom: 12px;">Add your degrees — you can add more than one</div>
                        <div id="degreeList"></div>
                        <button type="button" class="btn btn-secondary" style="width: 100%; padding: 10px; font-size: 13px;" onclick="addDegree()">＋ Add Degree</button>
                    </div>
                </form>
            </div>

            <!-- Step 5: Profile Picture -->
            <div class="form-step" id="step5">
                <h2 class="step-title">Profile Picture</h2>
                <p class="step-desc">Upload a clear photo — it helps people recognize you and makes your profile stand out.</p>

                <form>
                    <div class="form-group">
                        <div class="profile-pic-upload" onclick="document.getElementById('profilePic').click()">
                            <div class="pic-placeholder" id="picPlaceholder">📷</div>
                            <div class="pic-text">Click to upload photo</div>
                            <div class="pic-subtext">PNG, JPG, GIF up to 5MB</div>
                            <input type="file" id="profilePic" name="profilePic" accept="image/*" onchange="previewPicture(event)">
                            <div class="pic-preview" id="picPreview"></div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Navigation Buttons -->
            <div class="btn-group">
                <button type="button" class="btn btn-secondary" id="prevBtn" onclick="previousStep()" style="display: none;">← Back</button>
                <button type="button" class="btn btn-primary" id="nextBtn" onclick="nextStep()">Continue →</button>
            </div>
        </div>
    </div>

    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        let currentStep = 1;
        const totalSteps = 5;

        // Prefill verified email from the server
        document.addEventListener('DOMContentLoaded', async function() {
            try {
                const res = await fetch('/api/profile', { headers: { 'Accept': 'application/json' } });
                if (res.status === 401) { window.location.href = '/account'; return; }
                const json = await res.json();
                if (json.success) {
                    document.getElementById('emailStep2').value = json.data.email;
                }
            } catch (e) {}
            fetchGeneratedId('free');
            addDegree(); // start with one empty degree card
        });

        async function fetchGeneratedId(type) {
            try {
                const res = await fetch('/api/sohni-ids/generate?type=' + type, { headers: { 'Accept': 'application/json' } });
                const json = await res.json();
                if (json.success) {
                    document.getElementById('generatedId').value = json.data.sohni_id;
                    document.getElementById('idPreview').style.display = 'block';
                }
            } catch (e) {}
        }

        function updateProgress() {
            const percentage = (currentStep / totalSteps) * 100;
            document.getElementById('progressFill').style.width = percentage + '%';
            document.getElementById('progressText').textContent = `Step ${currentStep} of ${totalSteps}`;

            document.getElementById('prevBtn').style.display = currentStep > 1 ? 'block' : 'none';
            document.getElementById('nextBtn').textContent = currentStep === totalSteps ? '✓ Complete' : 'Continue →';
        }

        function showStep(step) {
            document.querySelectorAll('.form-step').forEach(el => el.classList.remove('active'));
            document.getElementById('step' + step).classList.add('active');
            updateProgress();
            window.scrollTo(0, 0);
        }

        function validateStep(step) {
            const inputs = document.getElementById('step' + step).querySelectorAll('input[required], select[required]');
            let isValid = true;

            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('error');
                    isValid = false;
                } else {
                    input.classList.remove('error');
                }
            });

            return isValid;
        }

        function nextStep() {
            if (!validateStep(currentStep)) {
                alert('Please fill in all required fields');
                return;
            }

            if (currentStep === totalSteps) {
                completeProfile();
                return;
            }

            currentStep++;
            showStep(currentStep);
        }

        function previousStep() {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        }

        function selectIdOption(radio) {
            document.querySelectorAll('.id-option').forEach(opt => opt.classList.remove('selected'));
            radio.closest('.id-option').classList.add('selected');

            document.getElementById('premiumPrice').style.display = radio.value === 'premium' ? 'block' : 'none';
            fetchGeneratedId(radio.value);
        }

        function previewPicture(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('picPreview');
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Profile">';
                    document.getElementById('picPlaceholder').textContent = '✓';
                };
                reader.readAsDataURL(file);
            }
        }

        // ============ Multi-degree education ============
        let degreeCount = 0;
        let experienceCount = 0;

        function addDegree() {
            degreeCount++;
            const card = document.createElement('div');
            card.className = 'degree-card';
            card.innerHTML = `
                <div class="degree-head">
                    <span class="degree-num">🎓 Degree</span>
                    <button type="button" class="degree-remove" onclick="this.closest('.degree-card').remove()" title="Remove">✕</button>
                </div>
                <input type="text" class="deg-title" placeholder="Title — e.g., BS Computer Science, LUMS">
                <div class="form-row">
                    <div class="form-group">
                        <input type="month" class="deg-date" title="Completion date">
                    </div>
                    <div class="form-group">
                        <input type="text" class="deg-grade" placeholder="Grade — e.g., A / 3.8 GPA">
                    </div>
                </div>
                <div class="form-row" style="margin-top:12px;">
                    <div class="form-group">
                        <input type="text" class="deg-marks" placeholder="Marks — e.g., 850/1100">
                    </div>
                    <div class="form-group"></div>
                </div>
            `;
            document.getElementById('degreeList').appendChild(card);
        }

        function collectDegrees() {
            return Array.from(document.querySelectorAll('.degree-card')).map(card => ({
                title: card.querySelector('.deg-title').value.trim(),
                completion_date: card.querySelector('.deg-date').value,
                grade: card.querySelector('.deg-grade').value.trim(),
                marks: card.querySelector('.deg-marks').value.trim()
            })).filter(d => d.title);
        }

        function addExperience() {
            experienceCount++;
            const card = document.createElement('div');
            card.className = 'degree-card experience-card';
            card.innerHTML = `
                <div class="degree-head">
                    <span class="degree-num">💼 Experience</span>
                    <button type="button" class="degree-remove" onclick="this.closest('.experience-card').remove()" title="Remove">✕</button>
                </div>
                <input type="text" class="exp-title" maxlength="150" placeholder="Job title — e.g., Software Engineer">
                <input type="text" class="exp-company" maxlength="150" placeholder="Company — e.g., Sohni Labs">
                <div class="form-row">
                    <div class="form-group"><input type="month" class="exp-start" title="Start date"></div>
                    <div class="form-group"><input type="month" class="exp-end" title="End date — leave blank if current"></div>
                </div>
                <textarea class="exp-description" maxlength="1000" rows="3" placeholder="Describe your responsibilities and achievements..."></textarea>
            `;
            document.getElementById('experienceList').appendChild(card);
        }

        function collectExperiences() {
            return Array.from(document.querySelectorAll('.experience-card')).map(card => ({
                title: card.querySelector('.exp-title').value.trim(),
                company: card.querySelector('.exp-company').value.trim(),
                start_date: card.querySelector('.exp-start').value,
                end_date: card.querySelector('.exp-end').value,
                description: card.querySelector('.exp-description').value.trim()
            })).filter(experience => experience.title);
        }

        async function completeProfile() {
            const formData = new FormData();
            formData.append('first_name', document.getElementById('firstName').value);
            formData.append('last_name', document.getElementById('lastName').value);
            formData.append('phone', document.getElementById('phone').value);
            formData.append('sohni_id_type', document.querySelector('input[name="sohniId"]:checked').value);
            formData.append('address', document.getElementById('address').value);
            formData.append('about_me', document.getElementById('aboutMe').value.trim());
            formData.append('experiences', JSON.stringify(collectExperiences()));
            formData.append('educations', JSON.stringify(collectDegrees()));
            const pic = document.getElementById('profilePic').files[0];
            if (pic) formData.append('profile_pic', pic);

            const btn = document.getElementById('nextBtn');
            btn.disabled = true;
            btn.textContent = 'Saving...';

            try {
                const res = await fetch('/api/profile/complete', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: formData
                });
                const json = await res.json();

                if (res.ok && json.success) {
                    btn.textContent = '✓ Profile Saved!';
                    setTimeout(() => { window.location.href = json.data.redirect || '/dashboard'; }, 800);
                } else {
                    const firstErr = json.errors ? Object.values(json.errors)[0][0] : (json.message || 'Something went wrong.');
                    alert('⚠ ' + firstErr);
                    btn.disabled = false;
                    btn.textContent = '✓ Complete';
                }
            } catch (e) {
                alert('⚠ Could not reach the server. Try again.');
                btn.disabled = false;
                btn.textContent = '✓ Complete';
            }
        }

        // Initialize
        updateProgress();
    </script>
</body>
</html>
