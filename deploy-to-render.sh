#!/bin/bash

echo "🚀 Deploying Fofana Stock Tracker to Render.com"
echo "================================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Check if git is initialized
if [ ! -d ".git" ]; then
    echo -e "${RED}❌ Git repository not found. Initializing...${NC}"
    git init
    git add .
    git commit -m "Initial commit for Render deployment"
fi

# Check for uncommitted changes
if [ -n "$(git status --porcelain)" ]; then
    echo -e "${YELLOW}⚠️  You have uncommitted changes. Committing them...${NC}"
    git add .
    git commit -m "Prepare for Render deployment - $(date)"
fi

# Check if remote origin exists
if ! git remote get-url origin > /dev/null 2>&1; then
    echo -e "${RED}❌ No git remote 'origin' found.${NC}"
    echo -e "${BLUE}📝 Please add your GitHub repository as origin:${NC}"
    echo "   git remote add origin https://github.com/yourusername/your-repo.git"
    echo "   git push -u origin main"
    exit 1
fi

echo -e "${GREEN}✅ Git repository is ready${NC}"

# Build and test Docker image locally
echo -e "${BLUE}🔨 Building Docker image locally for testing...${NC}"
docker build -t fofana-stock-tracker-test .

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Docker image built successfully${NC}"
else
    echo -e "${RED}❌ Docker build failed. Please fix the issues before deploying.${NC}"
    exit 1
fi

# Push to GitHub
echo -e "${BLUE}📤 Pushing to GitHub...${NC}"
git push origin main

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Code pushed to GitHub successfully${NC}"
else
    echo -e "${RED}❌ Failed to push to GitHub${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}🎉 Deployment preparation complete!${NC}"
echo ""
echo -e "${BLUE}📋 Next steps:${NC}"
echo "1. Go to https://render.com"
echo "2. Connect your GitHub repository"
echo "3. Render will automatically detect the render.yaml file"
echo "4. Your app will be deployed with Docker"
echo ""
echo -e "${YELLOW}📊 Your app will be available at:${NC}"
echo "https://fofana-stock-tracker.onrender.com"
echo ""
echo -e "${BLUE}🔑 Default login credentials:${NC}"
echo "SuperAdmin: nour@gmail.com / nouramara"
echo "Admin: aaaa@dev.com / nouramara"
echo ""
echo -e "${GREEN}✨ Happy deploying!${NC}"
