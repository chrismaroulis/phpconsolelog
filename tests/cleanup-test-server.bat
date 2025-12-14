@echo off
REM Cleanup script for test server processes
echo Stopping any PHP processes on port 8888...

powershell -Command "Get-NetTCPConnection -LocalPort 8888 -ErrorAction SilentlyContinue | ForEach-Object { Write-Host 'Killing process' $_.OwningProcess; Stop-Process -Id $_.OwningProcess -Force -ErrorAction SilentlyContinue }"

echo.
echo Cleanup complete!
echo.
pause
