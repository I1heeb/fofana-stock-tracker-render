#!/bin/bash

# 🌱 INITIAL DATABASE SETUP SCRIPT
# Run this ONLY ONCE to set up initial users and data
# DO NOT run on every deployment

echo "🌱 Setting up initial database data..."

# Seed the database with initial data
echo "👥 Creating initial users..."
php artisan db:seed --class=UserSeeder --force

echo "📦 Creating initial products..."
php artisan db:seed --class=ProductSeeder --force

echo "📋 Creating sample orders..."
php artisan db:seed --class=OrderSeeder --force

echo "📊 Creating sample logs..."
php artisan db:seed --class=LogSeeder --force

# Update existing users with plain passwords for super admin features
echo "🔑 Updating user passwords for super admin features..."
php artisan db:seed --class=UpdatePlainPasswordsSeeder --force

echo "✅ Initial database setup completed!"
echo "🚨 WARNING: Do not run this script again - it will overwrite data!"
echo "🔒 Future deployments will preserve all user data."
