#!/bin/sh
set -e

echo "🚀 Starting Laravel application..."
sleep 2

echo "🧹 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

echo "📦 Running migrations..."
php artisan migrate --force || echo "⚠️ Migrations failed"

echo "🌱 Seeding database..."
php artisan db:seed --force || echo "⚠️ Seeding skipped"

echo "🔗 Creating storage link..."
php artisan storage:link || echo "⚠️ Storage link exists"

echo "✅ Starting Apache..."
exec apache2-foreground