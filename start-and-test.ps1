Write-Host "Starting Laravel server..." -ForegroundColor Green
$job = Start-Job -ScriptBlock { php artisan serve }
Start-Sleep 10

Write-Host "Testing accessibility..." -ForegroundColor Yellow
npx pa11y http://127.0.0.1:8000/login --runner axe

Stop-Job $job