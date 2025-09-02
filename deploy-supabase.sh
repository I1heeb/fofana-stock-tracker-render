#!/bin/bash

# 🚀 SUPABASE DEPLOYMENT SCRIPT
# This script will be run on Render to set up Supabase database

echo "🚀 Starting Supabase Database Setup..."

# Set production environment
export APP_ENV=production

# Copy production environment file
cp .env.production .env

echo "✅ Environment configured for Supabase"

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "✅ Caches cleared"

# Run database migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

echo "✅ Database migrations completed"

# NEVER RUN SEEDERS ON DEPLOYMENT - PRESERVES USER DATA
echo "🔒 Skipping database seeding to preserve existing data"
echo "📊 Only running migrations - user data will be preserved"
echo "✅ Data preservation mode enabled"

# Note: To seed fresh database, run manually:
# php artisan db:seed --force

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Application optimized for production"

echo "🎉 Supabase deployment completed successfully!"
echo "📊 Database: PostgreSQL (Supabase)"
echo "🔗 Host: db.fiirszqosyhuhqbpb1y.supabase.co"
echo "🔒 SSL: Required"
echo "💾 Data Persistence: ✅ ENABLED"

# Test database connection
echo "🧪 Testing database connection..."
php artisan tinker --execute="echo 'Database connection: ' . DB::connection()->getPdo() ? 'SUCCESS' : 'FAILED';"

echo "🚀 Deployment script completed!"
