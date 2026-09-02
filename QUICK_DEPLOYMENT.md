# 🚀 SOHNI DEPLOYMENT - Quick Start Guide

## Where to Deploy (FREE)

### ⭐ **RECOMMENDED: Railway.app** (Best for Sohni)

**Why Railway?**
- ✅ $5/month free credit (enough for small-medium projects)
- ✅ Built-in Laravel support
- ✅ Auto-deploy from GitHub (push code → live in 2 min)
- ✅ Perfect for WebSocket/Reverb
- ✅ No special setup needed
- ✅ Can upgrade to paid later ($5-20/month)

**Cost:** FREE (with $5 credit)

---

## 🎯 Deployment in 5 Steps

### Step 1️⃣: Create GitHub Account
- Go to https://github.com
- Sign up (free)
- Create new repository called `sohni`

### Step 2️⃣: Push Your Code to GitHub
```bash
cd e:\mydata\website\sohni
git init
git add .
git commit -m "Sohni app with admin panel"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/sohni.git
git push -u origin main
```

**Replace `YOUR_USERNAME` with your GitHub username**

### Step 3️⃣: Create Railway Account
- Go to https://railway.app
- Click "Login with GitHub"
- Connect your GitHub account

### Step 4️⃣: Deploy with One Click
1. In Railway dashboard, click "New Project"
2. Select "Deploy from GitHub"
3. Choose your `sohni` repository
4. Click "Deploy"
5. Wait 2-3 minutes...
6. Done! 🎉

### Step 5️⃣: Configure Environment
1. Click on your project in Railway
2. Go to "Variables"
3. Add these variables:
   ```
   APP_URL = https://sohni-xxxxx.railway.app
   APP_KEY = base64:xxxxx  (from php artisan key:generate)
   DB_CONNECTION = sqlite
   DB_DATABASE = database/database.sqlite
   ```

---

## 🌐 Access Your Live App

After deployment, your app will be at:

```
Main Website:    https://sohni-xxxxx.railway.app
Admin Login:     https://sohni-xxxxx.railway.app/administrator/public/login.php

Admin Email:     superadmin@sohni.local
Admin Password:  ab40e6442f64234ce7 (change on first login)
```

---

## 📋 What's Included for Deployment

I've already created these files for you:

| File | Purpose |
|------|---------|
| `Procfile` | Tells Railway how to run your app |
| `runtime.txt` | Specifies PHP 8.2 |
| `.env.production` | Production environment config |
| `DEPLOY_TO_RAILWAY.md` | Detailed deployment guide |
| `deploy-railway.ps1` | Windows automated deployment script |
| `deploy-railway.sh` | Linux/Mac automated deployment script |

---

## 🔧 Pre-Deployment Checklist

- ✅ PHP 8.2 installed locally
- ✅ Composer dependencies installed
- ✅ Database migrations run (`php artisan migrate`)
- ✅ Admin panel tested locally
- ✅ Code committed to GitHub
- ✅ .env variables configured for production

---

## ⚠️ Important Notes

### SQLite Database
- ✅ Works perfectly on Railway
- ✅ Auto-created on first deploy
- ✅ Admin panel uses same database
- ✅ No external database needed (free!)

### Admin Panel
- ✅ Already configured
- ✅ Will work on Railway automatically
- ✅ All tables created via migrations
- ✅ Access at: `/administrator/public/login.php`

### WebSocket (Reverb)
- ✅ Railway supports WebSocket
- ✅ Will auto-configure on deployment
- ✅ Update REVERB_HOST after deployment

### File Uploads
- ✅ Works with local storage
- ✅ Good for up to ~100 users
- ✅ Scale to cloud storage (S3) later if needed

---

## 🆚 Alternative Free Options

| Platform | Cost | Setup Time | Best For |
|----------|------|-----------|----------|
| **Railway** | $5 credit/mo | 5 min | 🥇 Most Sohni apps |
| Render | Free | 10 min | Apps that can spin down |
| Replit | Free | 2 min | Testing/learning |
| Oracle Cloud | Free* | 30 min | Long-term projects |

*Oracle requires credit card but is truly free

---

## 💻 Automated Deployment Script

### Windows Users:
```powershell
cd e:\mydata\website\sohni
powershell -ExecutionPolicy Bypass -File deploy-railway.ps1
```

### Mac/Linux Users:
```bash
cd /path/to/sohni
bash deploy-railway.sh
```

These scripts will:
- ✅ Initialize Git
- ✅ Create .gitignore
- ✅ Commit all files
- ✅ Show you next steps

---

## 🎬 Quick Start Video Steps

1. **GitHub Setup** (2 min)
   - Create account
   - Create repo `sohni`
   - Note your username

2. **Push Code** (3 min)
   - Copy git commands above
   - Run in terminal
   - Wait for upload

3. **Railway Setup** (2 min)
   - Create account (login with GitHub)
   - Click "New Project"
   - Select your repo

4. **Deploy** (2-3 min)
   - Railway auto-deploys
   - Migrations run
   - App goes live!

**Total time: ~10 minutes** ⏱️

---

## 🚨 Troubleshooting

### "Build Failed" Error
**Solution:** Check Railway logs → fix errors → push new commit

### "Database Connection Error"
**Solution:** 
```bash
php artisan migrate --force
# Run locally first to test
```

### "Admin panel not accessible"
**Solution:** URL is `/administrator/public/login.php` not `/admin`

### "WebSocket not connecting"
**Solution:** Update REVERB_HOST in Railway variables to match your deployed domain

---

## 📞 Support

- Railway Docs: https://docs.railway.app
- Laravel Deployment: https://docs.railway.app/guides/laravel
- Sohni Docs: See DEPLOY_TO_RAILWAY.md in project

---

## ✅ After Deployment

Once live:

1. **Test Everything**
   - Visit main site
   - Test login (create test user)
   - Test admin panel
   - Test profile edit
   - Test 2FA setup

2. **Change Admin Password**
   - Login as superadmin@sohni.local
   - Go to "My Settings"
   - Change password to something secure

3. **Set Custom Domain** (Optional)
   - Go to Railway Project Settings
   - Add your custom domain
   - Point DNS to Railway

4. **Monitor Performance**
   - Railway dashboard shows usage
   - Monitor as users join
   - Upgrade if needed ($5-20/month)

---

## 💰 Cost Summary

| Item | Monthly Cost |
|------|---------------|
| Railway Free Tier | $0 (included $5 credit) |
| Custom Domain | $0 (optional DNS cost) |
| Database | $0 (SQLite included) |
| Admin Panel | $0 (included) |
| **Total** | **$0/month** 🎉 |

After $5 credit runs out:
- Stay free if usage is minimal
- Pay $5-20/month for reliable service
- Upgradeable as you grow

---

## 🎯 Ready to Launch?

**Choose your path:**

### Path 1: Automated (Easiest)
```bash
cd e:\mydata\website\sohni
# Windows
powershell -ExecutionPolicy Bypass -File deploy-railway.ps1
```

### Path 2: Manual (5 steps above)

### Path 3: Need Help?
Read `DEPLOY_TO_RAILWAY.md` for detailed instructions

---

## 🎉 That's It!

Your Sohni app will be live and accessible to the world in ~10 minutes!

Questions? DM me or check the deployment guides in your project folder.

**Happy deploying!** 🚀
