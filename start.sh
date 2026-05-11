#!/bin/sh

echo "Running migrations..."
php artisan migrate --force

echo "Seeding database..."
php artisan db:seed --force || echo "Seeding skipped (data exists)"

echo "Linking storage..."
php artisan storage:link || echo "Storage already linked"

echo "Starting Apache..."
apache2-foreground