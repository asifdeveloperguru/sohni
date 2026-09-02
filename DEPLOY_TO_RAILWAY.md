# Sohni Deployment Configuration

## Quick Start Deploy to Railway.app

### Step 1: Generate APP_KEY
```bash
php artisan key:generate
# Copy the key to .env.production APP_KEY=
```

### Step 2: Push to GitHub
```bash
cd frontend
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/sohni.git
git push -u origin main
```

### Step 3: Create Railway Project
1. Go to https://railway.app
2. Sign up (free with GitHub)
3. Click "New Project" → "Deploy from GitHub"
4. Select your sohni repository
5. Railway will auto-detect as PHP/Laravel

### Step 4: Configure Variables
In Railway dashboard, set environment variables:

```env
APP_KEY=base64:xxxxx  # From php artisan key:generate
APP_URL=https://sohni-xxxxx.railway.app

# Database (uses included SQLite)
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Admin Panel Database (same file)
# No additional config needed

# Optional: For file uploads
FILESYSTEM_DISK=local
```

### Step 5: Deploy & Test
1. Railway auto-deploys when you push
2. Check "Deployments" tab for status
3. View logs if there are errors
4. App runs via Apache (Procfile)
5. Migrations run automatically (release command in Procfile)

### Access Your App
```
Main app:        https://sohni-xxxxx.railway.app
Admin panel:     https://sohni-xxxxx.railway.app/administrator/public/login.php
Admin login:     superadmin@sohni.local
Admin password:  (change after first login)
```

---

## Files Included

- **Procfile** - Defines how to run the app (Apache + migrations)
- **runtime.txt** - Specifies PHP 8.2
- **.env.production** - Production environment variables

---

## Important Notes

### Database Files
- SQLite database file is auto-created at: `database/database.sqlite`
- Migrations run automatically on each deployment
- Admin panel uses same database

### Admin Panel
- URL: `/administrator/public/login.php`
- Already has all tables from migrations
- Create first admin:
  ```bash
  php artisan tinker
  # Or create via CLI before deploying
  ```

### WebSocket (Reverb)
- Configured for HTTPS with Railway's domain
- Update REVERB_HOST in Railway env vars to match your deployed URL

### File Uploads
- Uses local filesystem (storage/app)
- Works fine for small projects
- For production, consider cloud storage (S3, etc.)

---

## Troubleshooting

### Build Fails
Check logs in Railway dashboard. Common issues:
- Missing `.env` values
- Composer lock issues: `composer update --lock`
- PHP version mismatch: Ensure Procfile matches runtime.txt

### Database Connection Error
```bash
# Ensure directory exists and is writable
mkdir -p storage/logs
chmod -R 777 storage/
chmod -R 777 database/
```

### App Slow on Free Tier
- Railway free includes limited resources
- Consider upgrading to paid ($5-10/month) for production

---

## Local Testing Before Deploy

Test locally to ensure everything works:

```bash
cd frontend
php artisan serve
# Visit http://localhost:8000

# Test admin panel
php -S 127.0.0.1:9000 -t administrator/public

# Run migrations
php artisan migrate:fresh --seed
```

---

## Next Steps

1. ✅ Generate APP_KEY
2. ✅ Push code to GitHub
3. ✅ Connect Railway
4. ✅ Set environment variables
5. ✅ Deploy!

**Need help?** Railway docs: https://docs.railway.app/guides/laravel
