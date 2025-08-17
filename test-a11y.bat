@echo off
npx wait-on http://localhost:3000 --timeout 30000
if %errorlevel% equ 0 (
    npx pa11y http://localhost:3000 --runner axe
) else (
    echo Server not ready
)