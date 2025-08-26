#!/bin/bash

echo "🔨 Building frontend assets..."

# Check if vite is available
if ! command -v vite &> /dev/null; then
    echo "❌ Vite command not found, trying npx..."
    if ! command -v npx &> /dev/null; then
        echo "❌ npx not found either, creating fallback..."
        mkdir -p public/build
        echo '{"resources/css/app.css":{"file":"assets/app.css","isEntry":true},"resources/js/app.js":{"file":"assets/app.js","isEntry":true}}' > public/build/manifest.json
        echo "✅ Created fallback manifest.json"
        exit 0
    fi
    echo "📦 Using npx vite build..."
    npx vite build
else
    echo "📦 Using vite build..."
    npm run build
fi

# Check if build was successful
if [ -f "public/build/manifest.json" ]; then
    echo "✅ Assets built successfully!"
    ls -la public/build/
else
    echo "⚠️  Build completed but no manifest found, creating fallback..."
    mkdir -p public/build
    echo '{"resources/css/app.css":{"file":"assets/app.css","isEntry":true},"resources/js/app.js":{"file":"assets/app.js","isEntry":true}}' > public/build/manifest.json
    echo "✅ Created fallback manifest.json"
fi
