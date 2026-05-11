#!/bin/bash
echo "Nigerian CBT System - Quick Deploy Script"
echo "========================================"

# Check PHP version
php_version=$(php -r "echo PHP_VERSION;" 2>/dev/null)
if [ $? -ne 0 ]; then
    echo "ERROR: PHP is not installed or not in PATH"
    exit 1
fi

echo "PHP Version: $php_version"

# Install dependencies
echo "Installing Composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# Setup environment
if [ ! -f .env ]; then
    echo "Creating .env file..."
    cp .env.example .env
    php artisan key:generate
fi

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Seed database
echo "Seeding database..."
php artisan db:seed --force

# Cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "========================================"
echo "Deployment complete!"
echo "Run 'php artisan serve' to start the server"
