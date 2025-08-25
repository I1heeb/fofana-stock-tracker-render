# 🚀 Deploy Your Laravel App to Railway (FREE)

## Step 1: Login to Railway
```bash
railway login
```
This opens your browser - sign in with GitHub

## Step 2: Create New Project
```bash
railway link
```
Select "Create new project" and connect your GitHub repo

## Step 3: Set Environment Variables
```bash
# Copy your .env.example to Railway
railway variables set APP_NAME="Fofana Stock Tracker"
railway variables set APP_ENV=production
railway variables set APP_DEBUG=false
railway variables set APP_KEY=$(php artisan key:generate --show)

# Database (Railway provides free PostgreSQL)
railway add postgresql
# Railway auto-sets: DATABASE_URL, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD

# Your Supabase config (if you prefer Supabase over Railway's DB)
railway variables set DB_CONNECTION=pgsql
railway variables set DB_URL="your-supabase-url"
```

## Step 4: Deploy
```bash
railway up
```

## Step 5: Get Your Free Domain
After deployment, Railway gives you:
- **Free subdomain**: `yourapp.railway.app`
- **Custom domain option**: Connect your own domain later

## Step 6: Run Migrations
```bash
railway run php artisan migrate
railway run php artisan db:seed
```

## 🎉 Result:
- ✅ Free hosting (500 hours/month)
- ✅ Free domain: yourapp.railway.app
- ✅ Full Laravel functionality
- ✅ All your dependencies work
- ✅ Database included
- ✅ SSL certificate included

## Alternative Commands:
```bash
# View logs
railway logs

# Open in browser
railway open

# Run artisan commands
railway run php artisan tinker
```
