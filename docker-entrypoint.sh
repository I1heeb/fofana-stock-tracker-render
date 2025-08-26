#!/bin/bash
set -e

echo "🚀 Starting Laravel application setup..."

# Force production environment settings
echo "🔧 Setting production environment..."
export APP_ENV=production
export APP_DEBUG=false
export DB_CONNECTION=sqlite
export DB_DATABASE=/var/www/html/database/database.sqlite

# Debug environment variables
echo "🔍 Environment check:"
echo "APP_ENV: $APP_ENV"
echo "APP_DEBUG: $APP_DEBUG"
echo "DB_CONNECTION: $DB_CONNECTION"
echo "DB_DATABASE: $DB_DATABASE"

# Set up SQLite database FIRST (before any database operations)
echo "📁 Setting up SQLite database..."
mkdir -p /var/www/html/database
touch /var/www/html/database/database.sqlite
chown www-data:www-data /var/www/html/database/database.sqlite
chmod 664 /var/www/html/database/database.sqlite
echo "✅ SQLite database file created and configured"

# Wait for database to be ready (if using external DB)
if [ "$DB_CONNECTION" = "pgsql" ]; then
    echo "⏳ Waiting for PostgreSQL to be ready..."
    until pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME"; do
        echo "PostgreSQL is unavailable - sleeping"
        sleep 2
    done
    echo "✅ PostgreSQL is ready!"
fi

# Clear any cached configuration that might interfere
echo "🧹 Clearing ALL cached configuration..."
rm -rf /var/www/html/bootstrap/cache/config.php || true
rm -rf /var/www/html/bootstrap/cache/services.php || true
rm -rf /var/www/html/bootstrap/cache/packages.php || true
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Set proper file permissions for Laravel
echo "🔧 Setting proper file permissions..."
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# Force create a fresh config cache with our SQLite settings
echo "🔧 Creating fresh configuration cache..."
php artisan config:cache

# Verify database configuration before migration
echo "🔍 Verifying database configuration..."
php artisan config:show database.default || echo "Config show not available"

# Test database connection
echo "🔌 Testing database connection..."
php artisan migrate:status || echo "Migration status check failed, proceeding anyway..."

# Create a simple health check
echo "🏥 Creating health check..."
echo "<?php echo 'Laravel is working! Time: ' . date('Y-m-d H:i:s'); ?>" > /var/www/html/public/health.php

# Run database migrations
echo "📊 Running database migrations..."
php artisan migrate --force

# Seed database if needed (only in development)
if [ "$APP_ENV" = "local" ] || [ "$SEED_DATABASE" = "true" ]; then
    echo "🌱 Seeding database..."
    php artisan db:seed --force
fi

# Clear and cache config for production (minimal caching to avoid conflicts)
if [ "$APP_ENV" = "production" ]; then
    echo "⚡ Optimizing for production..."
    php artisan config:cache
    # Skip route:cache and view:cache to avoid deployment conflicts
fi

# Set proper permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "✅ Laravel application setup complete!"

# Execute the main command
exec "$@"
