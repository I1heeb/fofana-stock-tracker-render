#!/bin/bash
set -e

echo "🚀 Starting Laravel application setup..."

# Force production environment settings
echo "🔧 Setting production environment..."
export APP_ENV=production
export APP_DEBUG=false
export APP_KEY=base64:frICryS59HOmaoUtF03WgnrpFhnJSnkQlGROjzaePUI=

# FORCE SUPABASE POSTGRESQL CONNECTION (correct project ref)
echo "🔗 FORCING Supabase PostgreSQL database connection (CORRECT PROJECT)"
export DB_CONNECTION=pgsql
export DB_HOST=db.fiirszqosyhhuqbpbily.supabase.co
export DB_PORT=5432
export DB_DATABASE=postgres
export DB_USERNAME=postgres
export DB_PASSWORD=xhCtn3oRTksrcmc6
export DB_SSLMODE=require

echo "📊 Database configuration:"
echo "  DB_CONNECTION: $DB_CONNECTION"
echo "  DB_HOST: $DB_HOST"
echo "  DB_PORT: $DB_PORT"
echo "  DB_DATABASE: $DB_DATABASE"
echo "  DB_USERNAME: $DB_USERNAME"

# Force HTTPS for all URLs (fix mixed content on Render)
export FORCE_HTTPS=true
export APP_URL=https://fofana-stock-tracker-render.onrender.com

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

# Wait for database to be ready (if using external DB) with timeout
if [ "$DB_CONNECTION" = "pgsql" ]; then
    echo "⏳ Testing PostgreSQL connection (30 second timeout)..."

    # Try to connect with timeout
    timeout 30 bash -c "until pg_isready -h '$DB_HOST' -p '$DB_PORT' -U '$DB_USERNAME'; do echo 'PostgreSQL is unavailable - sleeping'; sleep 2; done" && {
        echo "✅ PostgreSQL is ready!"
    } || {
        echo "⚠️ PostgreSQL connection failed after 30 seconds"
        echo "🔄 Falling back to SQLite for deployment"
        export DB_CONNECTION=sqlite
        export DB_DATABASE=/var/www/html/database/database.sqlite
        echo "📊 Using fallback database: SQLite"
    }
fi

# Clear any cached configuration that might interfere
echo "🧹 Clearing ALL cached configuration..."
rm -rf /var/www/html/bootstrap/cache/config.php || true
rm -rf /var/www/html/bootstrap/cache/services.php || true
rm -rf /var/www/html/bootstrap/cache/packages.php || true
php artisan config:clear || true
php artisan cache:clear 2>/dev/null || echo "Cache clear skipped (no cache table)"
php artisan route:clear || true
php artisan view:clear || true

# Verify built assets exist
echo "🔍 Checking built assets..."
if [ -f "/var/www/html/public/build/manifest.json" ]; then
    echo "✅ Vite manifest found"
    ls -la /var/www/html/public/build/
else
    echo "❌ Vite manifest missing - assets may not load properly"
fi

# Set proper file permissions for Laravel
echo "🔧 Setting proper file permissions..."
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod -R 777 /var/www/html/storage
chmod -R 777 /var/www/html/bootstrap/cache
chmod -R 777 /var/www/html/database

# Create required directories if they don't exist
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
chmod -R 777 /var/www/html/storage/logs
chmod -R 777 /var/www/html/storage/framework

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# Force create fresh caches with our production settings
echo "🔧 Creating fresh configuration cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Verify database configuration before migration
echo "🔍 Verifying database configuration..."
php artisan config:show database.default || echo "Config show not available"

# Test database connection
echo "🔌 Testing database connection..."
php artisan migrate:status || echo "Migration status check failed, proceeding anyway..."

# Create a simple health check
echo "🏥 Creating health check..."
echo "<?php echo 'Laravel is working! Time: ' . date('Y-m-d H:i:s'); ?>" > /var/www/html/public/health.php

# Create a Laravel debug endpoint
echo "🔍 Creating Laravel debug endpoint..."
cat > /var/www/html/public/debug.php << 'EOF'
<?php
echo "<h1>Laravel Debug Info</h1>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>APP_KEY:</strong> " . (getenv('APP_KEY') ?: 'NOT SET') . "</p>";
echo "<p><strong>APP_ENV:</strong> " . (getenv('APP_ENV') ?: 'NOT SET') . "</p>";
echo "<p><strong>APP_DEBUG:</strong> " . (getenv('APP_DEBUG') ?: 'NOT SET') . "</p>";
echo "<p><strong>DB_CONNECTION:</strong> " . (getenv('DB_CONNECTION') ?: 'NOT SET') . "</p>";
echo "<p><strong>Storage writable:</strong> " . (is_writable('/var/www/html/storage') ? 'YES' : 'NO') . "</p>";
echo "<p><strong>Bootstrap cache writable:</strong> " . (is_writable('/var/www/html/bootstrap/cache') ? 'YES' : 'NO') . "</p>";
echo "<p><strong>Database exists:</strong> " . (file_exists('/var/www/html/database/database.sqlite') ? 'YES' : 'NO') . "</p>";

// Try to load Laravel
try {
    require_once '/var/www/html/vendor/autoload.php';
    $app = require_once '/var/www/html/bootstrap/app.php';
    echo "<p><strong>Laravel Bootstrap:</strong> SUCCESS</p>";

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "<p><strong>Kernel Creation:</strong> SUCCESS</p>";
} catch (Exception $e) {
    echo "<p><strong>Laravel Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
}
?>
EOF

# Run database migrations
echo "📊 Running database migrations..."
php artisan migrate --force

# NEVER SEED IN PRODUCTION - PRESERVES USER DATA
echo "🔒 Skipping database seeding to preserve user data"
echo "📊 Only migrations run - existing data preserved"
# Note: To seed fresh database, use /setup/initial-data route

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
