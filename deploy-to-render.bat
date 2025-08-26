@echo off
echo 🚀 Deploying Fofana Stock Tracker to Render.com
echo ================================================

REM Check if git is initialized
if not exist ".git" (
    echo ❌ Git repository not found. Initializing...
    git init
    git add .
    git commit -m "Initial commit for Render deployment"
)

REM Check for uncommitted changes
git status --porcelain > temp.txt
for /f %%i in ("temp.txt") do set size=%%~zi
del temp.txt
if %size% gtr 0 (
    echo ⚠️  You have uncommitted changes. Committing them...
    git add .
    git commit -m "Prepare for Render deployment - %date% %time%"
)

REM Check if remote origin exists
git remote get-url origin >nul 2>&1
if errorlevel 1 (
    echo ❌ No git remote 'origin' found.
    echo 📝 Please add your GitHub repository as origin:
    echo    git remote add origin https://github.com/yourusername/your-repo.git
    echo    git push -u origin main
    pause
    exit /b 1
)

echo ✅ Git repository is ready

REM Build and test Docker image locally
echo 🔨 Building Docker image locally for testing...
docker build -t fofana-stock-tracker-test .

if errorlevel 1 (
    echo ❌ Docker build failed. Please fix the issues before deploying.
    pause
    exit /b 1
)

echo ✅ Docker image built successfully

REM Push to GitHub
echo 📤 Pushing to GitHub...
git push origin main

if errorlevel 1 (
    echo ❌ Failed to push to GitHub
    pause
    exit /b 1
)

echo ✅ Code pushed to GitHub successfully
echo.
echo 🎉 Deployment preparation complete!
echo.
echo 📋 Next steps:
echo 1. Go to https://render.com
echo 2. Connect your GitHub repository
echo 3. Render will automatically detect the render.yaml file
echo 4. Your app will be deployed with Docker
echo.
echo 📊 Your app will be available at:
echo https://fofana-stock-tracker.onrender.com
echo.
echo 🔑 Default login credentials:
echo SuperAdmin: nour@gmail.com / nouramara
echo Admin: aaaa@dev.com / nouramara
echo.
echo ✨ Happy deploying!
pause
