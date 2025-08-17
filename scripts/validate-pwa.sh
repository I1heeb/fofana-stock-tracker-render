#!/bin/bash

echo "🚀 Starting PWA Validation..."

# Start Laravel server
echo "📡 Starting Laravel server..."
php artisan serve --port=8000 &
SERVER_PID=$!

# Wait for server to be ready
echo "⏳ Waiting for server..."
npx wait-on http://localhost:8000 --timeout 30000

if [ $? -ne 0 ]; then
    echo "❌ Server failed to start"
    kill $SERVER_PID 2>/dev/null
    exit 1
fi

echo "✅ Server is ready"

# Run PWA audit
echo "🔍 Running PWA Lighthouse audit..."
npm run test:pwa

PWA_RESULT=$?

# Run PWA Dusk tests
echo "🧪 Running PWA E2E tests..."
php artisan dusk --filter=PWA

DUSK_RESULT=$?

# Cleanup
echo "🧹 Cleaning up..."
kill $SERVER_PID 2>/dev/null

# Results
echo ""
echo "📊 PWA Validation Results:"
echo "=========================="

if [ $PWA_RESULT -eq 0 ]; then
    echo "✅ Lighthouse PWA Audit: PASSED"
else
    echo "❌ Lighthouse PWA Audit: FAILED"
fi

if [ $DUSK_RESULT -eq 0 ]; then
    echo "✅ PWA E2E Tests: PASSED"
else
    echo "❌ PWA E2E Tests: FAILED"
fi

# Exit with error if any test failed
if [ $PWA_RESULT -ne 0 ] || [ $DUSK_RESULT -ne 0 ]; then
    echo ""
    echo "❌ PWA validation failed!"
    exit 1
else
    echo ""
    echo "🎉 PWA validation successful!"
    exit 0
fi