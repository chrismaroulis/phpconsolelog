# Cleanup script for test server processes
Write-Host "Stopping any PHP processes on port 8888..." -ForegroundColor Yellow

$connections = Get-NetTCPConnection -LocalPort 8888 -ErrorAction SilentlyContinue

if ($connections) {
    foreach ($conn in $connections) {
        $processId = $conn.OwningProcess
        $process = Get-Process -Id $processId -ErrorAction SilentlyContinue
        if ($process) {
            Write-Host "Killing process $processId ($($process.ProcessName))" -ForegroundColor Cyan
            Stop-Process -Id $processId -Force -ErrorAction SilentlyContinue
        }
    }
    Write-Host "`nCleanup complete!" -ForegroundColor Green
} else {
    Write-Host "No processes found on port 8888" -ForegroundColor Green
}

Write-Host "`nPress any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
