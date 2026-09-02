@echo off
REM Deploy Sohni to Railway (Windows PowerShell Script)
REM Usage: powershell -ExecutionPolicy Bypass -File deploy-railway.ps1

Write-Host "🚀 Sohni Deployment Script for Railway.app" -ForegroundColor Green
Write-Host ""

# Check if git is installed
if (-not (Get-Command git -ErrorAction SilentlyContinue)) {
    Write-Host "❌ Git is not installed. Please install Git first." -ForegroundColor Red
    exit 1
}

# Initialize git repo
Write-Host "📦 Initializing Git repository..." -ForegroundColor Cyan
if (-not (Test-Path .git)) {
    git init
}

# Create .gitignore if not exists
if (-not (Test-Path .gitignore)) {
    Write-Host "📝 Creating .gitignore..."
    @"
/node_modules
/vendor
/.env
database/database.sqlite
"@ | Out-File -Encoding UTF8 .gitignore
}

# Add files to git
Write-Host "📝 Adding files to Git..." -ForegroundColor Cyan
git add .
git commit -m "Initial commit - Ready for Railway deployment"

# Rename branch to main
git branch -M main

Write-Host ""
Write-Host "✅ Ready to deploy!" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Create a GitHub account at https://github.com"
Write-Host "2. Create a new repository called 'sohni'"
Write-Host "3. Run these commands:"
Write-Host ""
Write-Host "   git remote add origin https://github.com/YOUR_USERNAME/sohni.git" -ForegroundColor Gray
Write-Host "   git push -u origin main" -ForegroundColor Gray
Write-Host ""
Write-Host "4. Go to https://railway.app" -ForegroundColor Cyan
Write-Host "5. Sign in with GitHub"
Write-Host "6. Click 'New Project' → 'Deploy from GitHub'"
Write-Host "7. Select your 'sohni' repository"
Write-Host "8. Railway will auto-deploy!"
Write-Host ""
Write-Host "Questions? See DEPLOY_TO_RAILWAY.md" -ForegroundColor Gray
