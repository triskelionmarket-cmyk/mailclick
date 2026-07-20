#!/bin/bash
# ============================================================
# MailClick Deploy Script — via Ploi SSH
# ============================================================
# Server: 89.167.94.212
# User: ploi
# Domain: app.mailclick.ro
# Server path: /home/ploi/app.mailclick.ro
# Public path: /home/ploi/app.mailclick.ro/public
# PHP version: 8.5
# Ploi panel: https://ploi.io/panel/servers/109414/sites/390063
# ============================================================

SSH_HOST="89.167.94.212"
SSH_USER="ploi"
APP_PATH="/home/ploi/app.mailclick.ro"

echo "🚀 Deploying MailClick to $SSH_HOST..."
echo "================================================"

ssh ${SSH_USER}@${SSH_HOST} bash -s <<'REMOTE'
set -e

APP_PATH="/home/ploi/app.mailclick.ro"
cd "$APP_PATH"

echo "📥 Pulling latest from origin/main..."
git pull origin main

echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo "📦 Running migrations (if any)..."
php artisan migrate --force

echo "✅ Deploy complete!"
echo "================================================"
echo "App path: $APP_PATH"
echo "Last commit:"
git log --oneline -1
echo "================================================"
REMOTE

echo "🎉 MailClick deploy finished!"
