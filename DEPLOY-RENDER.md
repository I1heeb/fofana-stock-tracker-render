# 🚀 Deploy Laravel Stock Tracker to Render

## ✅ Why Render is Perfect for Your App

- **FREE hosting** with 750 hours/month
- **FREE domain**: yourapp.onrender.com
- **Full Laravel support** - all your dependencies work
- **Excel processing** works perfectly
- **PWA features** fully supported
- **Pusher integration** works out of the box
- **PostgreSQL database** (30 days free, then $6/month)

## 🎯 Quick Deployment Steps

### Step 1: Sign Up & Connect Repository
1. Go to [render.com](https://render.com)
2. Sign up with GitHub (no credit card required)
3. Click "New +" → "Web Service"
4. Connect your GitHub repository: `nouramara123/fofana-stock-tracker`

### Step 2: Configure Service
Render will auto-detect Laravel. Verify these settings:
- **Name**: `fofana-stock-tracker`
- **Environment**: `PHP`
- **Build Command**: 
  ```bash
  composer install --no-dev --optimize-autoloader && npm ci && npm run build && php artisan config:cache && php artisan route:cache && php artisan view:cache
  ```
- **Start Command**: 
  ```bash
  php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
  ```

### Step 3: Add Database
1. Click "New +" → "PostgreSQL"
2. Name: `fofana-db`
3. Plan: **Free** (30 days, then $6/month)

### Step 4: Set Environment Variables
Add these in your web service settings:
```
APP_ENV=production
APP_DEBUG=false
APP_KEY=[Generate new key]
APP_URL=https://yourapp.onrender.com
DB_CONNECTION=pgsql
DB_HOST=[Auto-filled from database]
DB_PORT=[Auto-filled from database]
DB_DATABASE=[Auto-filled from database]
DB_USERNAME=[Auto-filled from database]
DB_PASSWORD=[Auto-filled from database]
```

### Step 5: Deploy
1. Click "Create Web Service"
2. Wait for build (5-10 minutes)
3. Your app will be live at: `https://yourapp.onrender.com`

## 🎉 Expected Results

- ✅ **Free domain**: `https://fofana-stock-tracker.onrender.com`
- ✅ **SSL certificate**: Automatic HTTPS
- ✅ **All features work**: Excel, PWA, Pusher, Charts
- ✅ **Database**: PostgreSQL with migrations
- ✅ **Cost**: $0 for first 30 days, then $6/month for database

## ⚠️ Important Notes

### Free Tier Limitations:
- **Service sleeps** after 15 minutes of inactivity
- **Cold start**: 30+ seconds to wake up
- **750 hours/month** limit (about 25 hours/day)

### Database Transition:
- **Days 1-30**: Completely free
- **Day 31+**: $6/month for database (web service stays free)

### Performance Tips:
- Keep your app active with uptime monitoring
- Consider upgrading to paid plan for production use
- Database cost is unavoidable after 30 days

## 🔧 Troubleshooting

### If Build Fails:
1. Check PHP version in composer.json (should be ^8.1 or ^8.2 for Render)
2. Ensure all dependencies are in composer.lock
3. Check build logs for specific errors

### If App Won't Start:
1. Verify APP_KEY is set
2. Check database connection variables
3. Ensure migrations run successfully

## 📊 Cost Projection

| Period | Web Service | Database | Total |
|--------|-------------|----------|-------|
| Month 1 | $0 | $0 | **$0** |
| Month 2+ | $0 | $6 | **$6/month** |
| Annual | $0 | $72 | **$72/year** |

This is the most cost-effective solution for your Laravel application with a free domain!
