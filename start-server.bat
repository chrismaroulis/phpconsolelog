@echo off
REM PHPConsoleLog Server Launcher (Windows Batch)
REM 
REM This script starts the PHPConsoleLog server.
REM 
REM Usage:
REM   start-server.bat              (default port 8080, all interfaces)
REM   start-server.bat 9000         (custom port, all interfaces)
REM   start-server.bat 9000 127.0.0.1  (custom port and host)

echo.
echo Starting PHPConsoleLog Server...
echo.

REM Check if PHP is available
where php >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: PHP is not found in your PATH.
    echo.
    echo Please install PHP or add it to your system PATH.
    echo Download PHP from: https://windows.php.net/download/
    echo.
    pause
    exit /b 1
)

REM Check if vendor directory exists
if not exist "%~dp0vendor" (
    echo ERROR: Vendor directory not found.
    echo.
    echo Please run: composer install
    echo.
    pause
    exit /b 1
)

REM Check if server.php exists
if not exist "%~dp0server.php" (
    echo ERROR: server.php not found in current directory.
    echo.
    echo Make sure you're running this script from the project root.
    echo.
    pause
    exit /b 1
)

REM Start the server with optional port and host arguments
php "%~dp0server.php" %1 %2

REM If the server exits, pause so user can see any error messages
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo Server stopped with an error.
    pause
)

