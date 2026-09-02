# Sohni App - Free Deployment Guide

## Tech Stack Requirements
- **Backend:** Laravel 11 + PHP 8.2
- **Frontend:** Laravel 11 Blade + JavaScript
- **Database:** SQLite
- **Real-time:** Reverb WebSocket server
- **Admin Panel:** Standalone PHP

---

## Best Free Hosting Options (Ranked)

### 🥇 **Option 1: Railway.app** ⭐ RECOMMENDED
**Why:** Best balance of free tier + Laravel support

**Pros:**
- $5/month free credit (enough for small projects)
- Built-in Laravel deployment
- Environment variables support
- SQLite database works
- WebSocket support
- GitHub integration

**Cons:**
- Credit runs out (need to pay for continuation)

**Cost:** $0/month (with $5 credit)

**Deploy in 5 minutes:**
```bash
# 1. Push code to GitHub
# 2. Connect Railway to GitHub repo
# 3. Add environment variables
# 4. Deploy
```

**Get Started:** https://railway.app

---

### 🥈 **Option 2: Render.com**
**Why:** Generous free tier, good documentation

**Pros:**
- Free tier available (with limitations)
- Auto-deploy from GitHub
- Environment variables
- Background jobs support
- WebSocket capable

**Cons:**
- Free tier spins down after 15 minutes of inactivity
- Limited database size
- Limited resources

**Cost:** Free (with 15-min inactivity spindown)

**Get Started:** https://render.com

---

### 🥉 **Option 3: Replit.com**
**Why:** Easiest to get started, free

**Pros:**
- No deployment needed
- Built-in editor + terminal
- Instant deployment
- Good for learning

**Cons:**
- Limited storage
- Limited bandwidth
- Always-free tier may have ads

**Cost:** Free (or $7/month for unlimited)

**Get Started:** https://replit.com

---

### 💎 **Option 4: Oracle Cloud (Always Free)**
**Why:** Generous always-free tier

**Pros:**
- Actually free (not time-limited)
- 2 VMs with 1GB RAM each
- Managed databases available
- Good performance

**Cons:**
- More complex setup
- Learning curve
- Needs credit card

**Cost:** Completely free (credit card required)

**Get Started:** https://www.oracle.com/cloud/free/

---

## Comparison Table

| Feature | Railway | Render | Replit | Oracle Cloud |
|---------|---------|--------|--------|--------------|
| Cost | $5 credit | Free | Free | Free |
| Laravel Support | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐ |
| WebSocket Support | ✓ | ✓ | Limited | ✓ |
| Uptime | 99.9% | 99.5% | ~95% | 99.9% |
| Auto-deploy | GitHub | GitHub | Built-in | Need setup |
| Database | SQLite OK | SQLite OK | Limited | PostgreSQL |
| Setup Time | 5 min | 10 min | 2 min | 30 min |

---

## 📋 Pre-Deployment Checklist

Before deploying, prepare these files:

### 1. **.env.production** (Create in project root)
```env
APP_NAME=Sohni
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app.railway.app

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_DRIVER=database

APP_KEY=base64:your-app-key-here

REVERB_APP_ID=123456
REVERB_APP_KEY=your-reverb-key
REVERB_APP_SECRET=your-reverb-secret
REVERB_HOST=your-app.railway.app
REVERB_PORT=443
REVERB_SCHEME=https
```

### 2. **Procfile** (Railway/Render need this)
```
web: vendor/bin/heroku-php-apache2 public/
release: php artisan migrate --force
```

### 3. **runtime.txt** (specify PHP version)
```
php-8.2
```

### 4. **Composer.json** (ensure updated)
```json
{
  "require": {
    "php": "^8.2",
    "laravel/framework": "^11.0"
  }
}
```

---

## Step-by-Step: Deploy to Railway (Easiest)

### Step 1: Prepare Your Code
```bash
cd e:\mydata\website\sohni\frontend
git init
git add .
git commit -m "Initial commit"
git branch -M main
```

### Step 2: Push to GitHub
```bash
# Create repo on GitHub.com first
git remote add origin https://github.com/YOUR_USERNAME/sohni.git
git push -u origin main
```

### Step 3: Connect to Railway
1. Go to https://railway.app
2. Sign up (free)
3. Click "New Project"
4. Select "Deploy from GitHub"
5. Choose your `sohni` repo
6. Click "Deploy"

### Step 4: Configure Environment
1. Go to project settings
2. Add environment variables (from .env.production)
3. Add service for Admin Panel (optional)

### Step 5: Run Migrations
1. In Railway dashboard
2. Click "Deployments" 
3. View logs during deployment
4. Migrations run automatically via `release` command in Procfile

### Step 6: Access Your App
```
https://sohni-production-xyz.railway.app
Admin panel: https://sohni-production-xyz.railway.app/administrator/public/login.php
```

---

## Common Issues & Fixes

### Issue: "SQLSTATE[HY000]: General error: unable to open database file"
**Solution:** SQLite path issue
```php
// In .env
DB_DATABASE=/app/database/database.sqlite

// Ensure directory exists
mkdir -p database/
chmod 777 database/
```

### Issue: "Class not found" errors
**Solution:** Autoloader issue
```bash
php artisan optimize:clear
composer dump-autoload -o
```

### Issue: WebSocket not connecting
**Solution:** Reverb URL configuration
```env
# Use HTTPS for web
REVERB_SCHEME=https
REVERB_PORT=443

# Domain must match
REVERB_HOST=your-app-name.railway.app
```

### Issue: Uploads not persisting
**Solution:** Use cloud storage instead of local
```env
FILESYSTEM_DISK=s3
AWS_BUCKET=your-bucket
# Or use cloud storage provider
```

---

## Recommended Setup: Railway + GitHub

### Architecture:
```
GitHub Repo
    ↓
    └─→ Push to main branch
         ↓
         └─→ Railway detects
              ↓
              └─→ Automatic deployment
                   ↓
                   └─→ Runs migrations
                        ↓
                        └─→ App live!
```

### Workflow:
```bash
# After making changes locally
git add .
git commit -m "Update admin features"
git push origin main

# Railway automatically deploys!
```

---

## Budget Breakdown (Monthly)

### Railway ($5 credit/month)
- CPU: Usually free
- Memory: Usually free  
- Storage: Usually free
- **Total:** $0/month (with credit)

### After credit runs out:
- ~$5-10/month (if you continue)

### Render.com (Free)
- Spin-down after 15 min: Free
- Keep always-on: ~$7/month

### Oracle Cloud Always-Free
- 2 VMs: Free forever
- Database: Free (1GB)
- **Total:** $0/month forever

---

## Next Steps

1. **Choose platform** → I recommend Railway
2. **Prepare code** → Add Procfile + .env.production
3. **Push to GitHub** → Create repo
4. **Connect to Railway** → Follow step-by-step above
5. **Test deployment** → Visit your live URL
6. **Monitor logs** → Use Railway dashboard

---

## Support Resources

**Railway:**
- Docs: https://docs.railway.app
- Laravel: https://docs.railway.app/guides/laravel

**Render:**
- Docs: https://render.com/docs
- Laravel: https://render.com/docs/deploy-laravel

**Replit:**
- Docs: https://docs.replit.com
- Laravel: https://docs.replit.com/tutorials/php

**Oracle Cloud:**
- Free Tier: https://www.oracle.com/cloud/free/
- Getting Started: https://docs.oracle.com/en-us/iaas/Content/GSG/Concepts/baremetalintro.htm

---

## Quick Comparison Summary

| Metric | Railway | Render | Replit | Oracle |
|--------|---------|--------|--------|--------|
| **Ease** | Easy | Medium | Very Easy | Complex |
| **Cost** | $5 credit | Free | Free | Free* |
| **Performance** | High | Good | Okay | High |
| **WebSocket** | Yes | Yes | Limited | Yes |
| **Time to Deploy** | 5 min | 10 min | 2 min | 30 min |
| **Recommended** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐ |

\* Oracle requires credit card but is truly free

---

## Make Your Choice

**I recommend Railway because:**
1. ✅ Easiest for Laravel
2. ✅ Best free-to-paid transition
3. ✅ Excellent documentation
4. ✅ Good performance
5. ✅ Just 5 minutes to deploy

**Ready to deploy?** Let me know which platform you choose and I'll help you through the process! 🚀
