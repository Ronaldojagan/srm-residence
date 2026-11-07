@echo off
title SRM Residence - Live Global Link Launcher
echo ========================================================
echo        SRM RESIDENCE - LIVE GLOBAL LINK LAUNCHER
echo ========================================================
echo.

:: Check if Laravel server is running on port 8000
netstat -ano | findstr :8000 | findstr LISTENING >nul
if %errorlevel% neq 0 (
    echo [1/2] Starting local Laravel application server...
    start "SRM Residence Server" /min cmd /c "php artisan serve --port=8000"
    timeout /t 3 /nobreak >nul
) else (
    echo [1/2] Local application server is already active on port 8000.
)

echo [2/2] Connecting to secure Cloudflare Global Tunnel...
echo.
echo --------------------------------------------------------
echo  Your live shareable link will appear below:
echo  (Look for https://xxxxxxxx.trycloudflare.com)
echo --------------------------------------------------------
echo.
"C:\Users\jagan\cloudflared.exe" tunnel --url http://127.0.0.1:8000
pause
