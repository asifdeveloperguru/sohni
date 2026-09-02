<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit Profile - Sohni</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Inter:wght@400;500;600&family=Space+Grotesk:wght@700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(180deg, #eef4fb 0%, #f5f7fa 320px, #f5f7fa 100%);
            color: #262626;
            min-height: 100vh;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 28px 20px 60px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding: 18px 24px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.06);
            border: 1px solid #eef1f5;
        }

        .header h1 {
            font-family: 'Sora', sans-serif;
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(135deg, #0084ff, #00b8ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header h1 i {
            -webkit-text-fill-color: #0084ff;
            color: #0084ff;
        }

        .header-buttons {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 11px 22px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'Inter', sans-serif;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0084ff, #00c3ff);
            color: white;
            box-shadow: 0 4px 14px rgba(0, 132, 255, 0.28);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(0, 132, 255, 0.38);
        }

        .btn-secondary {
            background: #f0f2f5;
            color: #444;
            border: 1px solid #e2e6ea;
        }

        .btn-secondary:hover {
            background: #e4e8ec;
        }

        .form-section {
            background: #ffffff;
            border-radius: 16px;
            padding: 28px;
            margin-bottom: 22px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
            border: 1px solid #eef1f5;
            transition: box-shadow 0.25s ease;
        }

        .form-section:hover {
            box-shadow: 0 6px 22px rgba(15, 23, 42, 0.08);
        }

        .section-title {
            font-family: 'Sora', sans-serif;
            font-size: 17px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 22px;
            padding-bottom: 14px;
            border-bottom: 2px solid #eef1f5;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #0084ff;
            font-size: 16px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #444;
            font-size: 13px;
            letter-spacing: 0.2px;
        }

        .form-group input,
        .form-group textarea {
            padding: 12px 14px;
            border: 1.5px solid #e2e6ea;
            border-radius: 10px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            background: #fafbfc;
            transition: all 0.25s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #0084ff;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(0, 132, 255, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .image-upload {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .image-upload input[type="file"] {
            display: none;
        }

        .image-upload-btn {
            padding: 13px;
            border: 2px dashed #0084ff;
            border-radius: 10px;
            background: #f0f8ff;
            cursor: pointer;
            text-align: center;
            font-weight: 600;
            color: #0084ff;
            font-size: 13px;
            transition: all 0.25s ease;
        }

        .image-upload-btn:hover {
            background: #e0f0ff;
            transform: translateY(-1px);
        }

        .image-preview {
            position: relative;
            width: 100%;
            height: 150px;
            border-radius: 10px;
            overflow: hidden;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1.5px solid #e2e6ea;
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-preview .remove-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(15, 23, 42, 0.65);
            color: white;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            transition: all 0.2s ease;
            backdrop-filter: blur(2px);
        }

        .image-preview .remove-btn:hover {
            background: #ff4444;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        .education-container {
            margin-top: 20px;
        }

        .education-card {
            background: #f8fafc;
            border-left: 4px solid #0084ff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            position: relative;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        }

        .education-card .remove-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            background: #ffffff;
            color: #ff4444;
            border: 1.5px solid #ffd6d6;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .education-card .remove-btn:hover {
            background: #ff4444;
            color: white;
            border-color: #ff4444;
        }

        .add-degree-btn {
            padding: 13px 20px;
            background: #f0f8ff;
            color: #0084ff;
            border: 2px dashed #0084ff;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.25s ease;
            width: 100%;
        }

        .add-degree-btn:hover {
            background: #e0f0ff;
            transform: translateY(-1px);
        }

        .form-footer {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .message {
            padding: 15px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: none;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
        }

        .message.success {
            background: #eafaf0;
            color: #1a7a3e;
            border: 1px solid #bfe8cd;
            display: block;
        }

        .message.error {
            background: #fdecec;
            color: #b3261e;
            border: 1px solid #f6c3c0;
            display: block;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #0084ff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .form-footer {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-user-edit"></i> Edit Profile</h1>
            <div class="header-buttons">
                <button class="btn btn-secondary" onclick="goBack()">
                    <i class="fas fa-arrow-left"></i> Back to Profile
                </button>
            </div>
        </div>

        <div id="message" class="message"></div>
        <div id="loading" class="loading">
            <div class="spinner"></div>
            <p>Saving changes...</p>
        </div>

        <form id="profileForm">
            <!-- Personal Info -->
            <div class="form-section">
                <h2 class="section-title"><i class="fas fa-user"></i> Personal Information</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="firstName">First Name</label>
                        <input type="text" id="firstName" name="first_name" placeholder="John">
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <input type="text" id="lastName" name="last_name" placeholder="Doe">
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" placeholder="+92 300 1234567">
                    </div>
                    <div class="form-group full-width">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" placeholder="Your address..."></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label for="aboutMe">About Me</label>
                        <textarea id="aboutMe" name="about_me" maxlength="2000" rows="5" placeholder="Tell people about your skills, goals, and interests..."></textarea>
                        <small>Maximum 2,000 characters.</small>
                    </div>
                </div>
            </div>

            <!-- Experience -->
            <div class="form-section">
                <h2 class="section-title"><i class="fas fa-briefcase"></i> Work Experience</h2>
                <div id="experienceContainer" class="education-container"></div>
                <button type="button" class="add-degree-btn" onclick="addExperience()">
                    <i class="fas fa-plus"></i> Add Experience
                </button>
            </div>

            <!-- Images -->
            <div class="form-section">
                <h2 class="section-title"><i class="fas fa-images"></i> Profile Images</h2>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Profile Picture</label>
                        <div class="image-upload">
                            <button type="button" class="image-upload-btn" onclick="document.getElementById('avatarInput').click()">
                                <i class="fas fa-cloud-upload-alt"></i> Choose Avatar
                            </button>
                            <input type="file" id="avatarInput" name="profile_pic" accept="image/*">
                            <div id="avatarPreview" class="image-preview"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Cover Banner</label>
                        <div class="image-upload">
                            <button type="button" class="image-upload-btn" onclick="document.getElementById('coverInput').click()">
                                <i class="fas fa-cloud-upload-alt"></i> Choose Banner
                            </button>
                            <input type="file" id="coverInput" name="cover_image" accept="image/*">
                            <div id="coverPreview" class="image-preview"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Education -->
            <div class="form-section">
                <h2 class="section-title"><i class="fas fa-graduation-cap"></i> Education & Qualifications</h2>
                <div id="educationContainer" class="education-container"></div>
                <button type="button" class="add-degree-btn" onclick="addDegree()">
                    <i class="fas fa-plus"></i> Add Degree
                </button>
            </div>

            <!-- Submit -->
            <div class="form-footer">
                <button type="button" class="btn btn-secondary" onclick="goBack()">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>

    <script>
        let profileData = {};
        let educationsList = [];
        let experiencesList = [];

        document.addEventListener('DOMContentLoaded', loadProfile);
        document.getElementById('profileForm').addEventListener('submit', handleSubmit);
        document.getElementById('avatarInput').addEventListener('change', previewAvatar);
        document.getElementById('coverInput').addEventListener('change', previewCover);

        async function loadProfile() {
            try {
                const response = await fetch('/api/profile', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                    }
                });

                if (!response.ok) throw new Error('Failed to load profile');

                const result = await response.json();
                profileData = result.data;
                educationsList = profileData.educations || [];
                experiencesList = profileData.experiences || [];

                // Populate form
                document.getElementById('firstName').value = profileData.first_name || '';
                document.getElementById('lastName').value = profileData.last_name || '';
                document.getElementById('phone').value = profileData.phone || '';
                document.getElementById('address').value = profileData.address || '';
                document.getElementById('aboutMe').value = profileData.about_me || '';

                // Load avatar preview
                if (profileData.avatar_url) {
                    const avatarPreview = document.getElementById('avatarPreview');
                    avatarPreview.innerHTML = `<img src="${profileData.avatar_url}" alt="Avatar">
                        <button type="button" class="remove-btn" onclick="removeAvatar(event)">
                            <i class="fas fa-trash"></i>
                        </button>`;
                }

                // Load cover preview
                if (profileData.cover_url) {
                    const coverPreview = document.getElementById('coverPreview');
                    coverPreview.innerHTML = `<img src="${profileData.cover_url}" alt="Cover">
                        <button type="button" class="remove-btn" onclick="removeCover(event)">
                            <i class="fas fa-trash"></i>
                        </button>`;
                }

                // Load educations
                renderEducations();
                renderExperiences();
            } catch (error) {
                showMessage('Error loading profile: ' + error.message, 'error');
            }
        }

        function renderEducations() {
            const container = document.getElementById('educationContainer');
            container.innerHTML = '';

            educationsList.forEach((edu, index) => {
                const card = document.createElement('div');
                card.className = 'education-card';
                card.innerHTML = `
                    <button type="button" class="remove-btn" onclick="removeDegree(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label>Degree/Title</label>
                            <input type="text" value="${edu.title || ''}" onchange="updateEducation(${index}, 'title', this.value)" placeholder="e.g., Bachelor of Science">
                        </div>
                        <div class="form-group">
                            <label>Completion Date</label>
                            <input type="text" value="${edu.completion_date || ''}" onchange="updateEducation(${index}, 'completion_date', this.value)" placeholder="e.g., 2023">
                        </div>
                        <div class="form-group">
                            <label>Grade/GPA</label>
                            <input type="text" value="${edu.grade || ''}" onchange="updateEducation(${index}, 'grade', this.value)" placeholder="e.g., 3.8">
                        </div>
                        <div class="form-group">
                            <label>Marks/Score</label>
                            <input type="text" value="${edu.marks || ''}" onchange="updateEducation(${index}, 'marks', this.value)" placeholder="e.g., 85%">
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        function addDegree() {
            educationsList.push({ title: '', completion_date: '', grade: '', marks: '' });
            renderEducations();
        }

        function removeDegree(index) {
            educationsList.splice(index, 1);
            renderEducations();
        }

        function updateEducation(index, field, value) {
            educationsList[index][field] = value;
        }

        function renderExperiences() {
            const container = document.getElementById('experienceContainer');
            container.innerHTML = '';

            experiencesList.forEach((experience, index) => {
                const card = document.createElement('div');
                card.className = 'education-card';
                card.innerHTML = `
                    <button type="button" class="remove-btn" onclick="removeExperience(${index})"><i class="fas fa-trash"></i></button>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Job Title</label>
                            <input type="text" value="${esc(experience.title)}" maxlength="150" onchange="updateExperience(${index}, 'title', this.value)" placeholder="e.g., Product Designer">
                        </div>
                        <div class="form-group">
                            <label>Company</label>
                            <input type="text" value="${esc(experience.company)}" maxlength="150" onchange="updateExperience(${index}, 'company', this.value)" placeholder="e.g., Sohni Labs">
                        </div>
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="month" value="${esc(experience.start_date)}" onchange="updateExperience(${index}, 'start_date', this.value)">
                        </div>
                        <div class="form-group">
                            <label>End Date</label>
                            <input type="month" value="${esc(experience.end_date)}" onchange="updateExperience(${index}, 'end_date', this.value)" placeholder="Leave blank for current">
                        </div>
                        <div class="form-group full-width">
                            <label>Description</label>
                            <textarea maxlength="1000" rows="3" onchange="updateExperience(${index}, 'description', this.value)" placeholder="Describe your responsibilities and achievements...">${esc(experience.description)}</textarea>
                        </div>
                    </div>`;
                container.appendChild(card);
            });
        }

        function addExperience() {
            experiencesList.push({ title: '', company: '', start_date: '', end_date: '', description: '' });
            renderExperiences();
        }

        function removeExperience(index) {
            experiencesList.splice(index, 1);
            renderExperiences();
        }

        function updateExperience(index, field, value) {
            experiencesList[index][field] = value;
        }

        function esc(value) {
            return String(value || '').replace(/[&<>"']/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[character]));
        }

        function previewAvatar(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    const preview = document.getElementById('avatarPreview');
                    preview.innerHTML = `<img src="${event.target.result}" alt="Avatar">
                        <button type="button" class="remove-btn" onclick="removeAvatar(event)">
                            <i class="fas fa-trash"></i>
                        </button>`;
                };
                reader.readAsDataURL(file);
            }
        }

        function previewCover(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    const preview = document.getElementById('coverPreview');
                    preview.innerHTML = `<img src="${event.target.result}" alt="Cover">
                        <button type="button" class="remove-btn" onclick="removeCover(event)">
                            <i class="fas fa-trash"></i>
                        </button>`;
                };
                reader.readAsDataURL(file);
            }
        }

        function removeAvatar(e) {
            e.preventDefault();
            document.getElementById('avatarInput').value = '';
            document.getElementById('avatarPreview').innerHTML = '';
        }

        function removeCover(e) {
            e.preventDefault();
            document.getElementById('coverInput').value = '';
            document.getElementById('coverPreview').innerHTML = '';
        }

        async function handleSubmit(e) {
            e.preventDefault();

            const formData = new FormData();
            formData.append('first_name', document.getElementById('firstName').value);
            formData.append('last_name', document.getElementById('lastName').value);
            formData.append('phone', document.getElementById('phone').value);
            formData.append('address', document.getElementById('address').value);
            formData.append('about_me', document.getElementById('aboutMe').value.trim());
            formData.append('experiences', JSON.stringify(experiencesList));
            formData.append('educations', JSON.stringify(educationsList));

            if (document.getElementById('avatarInput').files.length > 0) {
                formData.append('profile_pic', document.getElementById('avatarInput').files[0]);
            }

            if (document.getElementById('coverInput').files.length > 0) {
                formData.append('cover_image', document.getElementById('coverInput').files[0]);
            }

            showLoading(true);

            try {
                const response = await fetch('/api/profile/update', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    }
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Failed to update profile');
                }

                showMessage('Profile updated successfully!', 'success');
                setTimeout(() => {
                    window.location.href = '/profile';
                }, 1500);
            } catch (error) {
                showMessage('Error: ' + error.message, 'error');
            } finally {
                showLoading(false);
            }
        }

        function showMessage(text, type) {
            const msg = document.getElementById('message');
            msg.textContent = text;
            msg.className = 'message ' + type;
            setTimeout(() => {
                msg.style.display = 'none';
            }, 5000);
        }

        function showLoading(show) {
            document.getElementById('loading').style.display = show ? 'block' : 'none';
        }

        function goBack() {
            window.location.href = '/profile';
        }
    </script>
</body>
</html>
