Write-Host "🚀 Starting PWA Validation..." -ForegroundColor Green

# Build assets
Write-Host "📦 Building assets..." -ForegroundColor Yellow
npm run build

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Build failed" -ForegroundColor Red
    exit 1
}

# Start server in background
Write-Host "📡 Starting Laravel server..." -ForegroundColor Yellow
$job = Start-Job -ScriptBlock { php artisan serve --port=8000 }
Start-Sleep 5

# Test if server is running
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8000" -TimeoutSec 10
    Write-Host "✅ Server is ready" -ForegroundColor Green
} catch {
    Write-Host "❌ Server failed to start" -ForegroundColor Red
    Stop-Job $job
    exit 1
}

Write-Host "🎉 PWA validation setup complete!" -ForegroundColor Green
Stop-Job $job