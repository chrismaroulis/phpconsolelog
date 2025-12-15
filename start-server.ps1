#!/usr/bin/env pwsh
<#
.SYNOPSIS
    PHPConsoleLog Server Launcher (PowerShell)

.DESCRIPTION
    This script starts the PHPConsoleLog server.
    
.PARAMETER Port
    The port to run the server on (default: 8080)

.PARAMETER Host
    The host to bind to (default: 0.0.0.0 for all interfaces, use 127.0.0.1 for localhost only)

.EXAMPLE
    .\start-server.ps1
    Start server on default port 8080, all interfaces

.EXAMPLE
    .\start-server.ps1 -Port 9000
    Start server on port 9000, all interfaces

.EXAMPLE
    .\start-server.ps1 -Port 9000 -Host "127.0.0.1"
    Start server on port 9000, localhost only
#>

param(
    [int]$Port = 8080,
    [string]$Host = "0.0.0.0"
)

Write-Host ""
Write-Host "Starting PHPConsoleLog Server..." -ForegroundColor Cyan
Write-Host ""

# Get script directory
$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path

# Check if PHP is available
$phpCommand = Get-Command php -ErrorAction SilentlyContinue
if (-not $phpCommand) {
    Write-Host "ERROR: PHP is not found in your PATH." -ForegroundColor Red
    Write-Host ""
    Write-Host "Please install PHP or add it to your system PATH."
    Write-Host "Download PHP from: https://windows.php.net/download/"
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}

# Check PHP version
$phpVersion = & php -r "echo PHP_VERSION;"
Write-Host "PHP Version: $phpVersion" -ForegroundColor Green

# Check if vendor directory exists
$vendorPath = Join-Path $ScriptDir "vendor"
if (-not (Test-Path $vendorPath)) {
    Write-Host ""
    Write-Host "ERROR: Vendor directory not found." -ForegroundColor Red
    Write-Host ""
    Write-Host "Please run: composer install"
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}

# Check if server.php exists
$serverPath = Join-Path $ScriptDir "server.php"
if (-not (Test-Path $serverPath)) {
    Write-Host ""
    Write-Host "ERROR: server.php not found." -ForegroundColor Red
    Write-Host ""
    Write-Host "Make sure you're running this script from the project root."
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}

# Start the server
Write-Host ""
Write-Host "Starting server on $Host`:$Port..." -ForegroundColor Yellow
Write-Host ""

try {
    & php $serverPath $Port $Host
} catch {
    Write-Host ""
    Write-Host "ERROR: Failed to start server." -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}

