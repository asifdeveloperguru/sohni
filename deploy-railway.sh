#!/bin/bash
# Deploy Sohni to Railway (Quick Script)
# Usage: bash deploy-railway.sh

echo "🚀 Sohni Deployment Script for Railway.app"
echo ""

# Step 1: Check if git is installed
if ! command -v git &> /dev/null; then
    echo "❌ Git is not installed. Please install Git first."
    exit 1
fi

# Step 2: Generate APP_KEY if not exists
if ! grep -q "^APP_KEY=" frontend/.env.production; then
    echo "📝 Generating APP_KEY..."
    cd frontend
    php artisan key:generate --show | sed 's/^/APP_KEY=/' > /tmp/key.txt
    cd ..
    echo "✓ Generated APP_KEY"
fi

# Step 3: Initialize git repo
echo ""
echo "📦 Initializing Git repository..."
git init

# Step 4: Create .gitignore if not exists
if [ ! -f .gitignore ]; then
    echo "/node_modules" > .gitignore
    echo "/vendor" >> .gitignore
    echo "/.env" >> .gitignore
    echo "database/database.sqlite" >> .gitignore
fi

# Step 5: Add files to git
echo "📝 Adding files to Git..."
git add .
git commit -m "Initial commit - Ready for Railway deployment"

# Step 6: Rename branch to main
git branch -M main

echo ""
echo "✅ Ready to deploy!"
echo ""
echo "Next steps:"
echo "1. Create a GitHub account at https://github.com"
echo "2. Create a new repository called 'sohni'"
echo "3. Run these commands:"
echo ""
echo "   git remote add origin https://github.com/YOUR_USERNAME/sohni.git"
echo "   git push -u origin main"
echo ""
echo "4. Go to https://railway.app"
echo "5. Sign in with GitHub"
echo "6. Click 'New Project' → 'Deploy from GitHub'"
echo "7. Select your 'sohni' repository"
echo "8. Railway will auto-deploy!"
echo ""
echo "Questions? See DEPLOY_TO_RAILWAY.md"
