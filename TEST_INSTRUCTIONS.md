# Testing Instructions

Before deploying to GitHub, please test your PHPConsoleLog server locally.

## Prerequisites

### First Time Setup

If you haven't installed dependencies yet, run:

**Windows:**
```bash
setup.bat
```

**Linux/Mac:**
```bash
composer install
```

This will install all required dependencies (Ratchet, Guzzle, etc.) in the `vendor/` directory.

**Note:** The `vendor/` directory is already in `.gitignore` and will NOT be uploaded to GitHub. ✅

---

## Quick Test (Recommended)

The fastest way to verify everything works:

### Step 1: Start the Server

Open a terminal and run:

```bash
php examples/server-start.php
```

You should see:
```
╔═══════════════════════════════════════════════════════════════╗
║              PHPConsoleLog Server                             ║
╚═══════════════════════════════════════════════════════════════╝

Starting server on 0.0.0.0:8080
...
Server is running...
```

### Step 2: Run Quick Test

Open a **second terminal** and run:

```bash
php tests/quick-test.php
```

This will:
- ✅ Check if the server is running
- 📤 Send various test messages
- 🎨 Test different log levels and data types

### Step 3: View the Results

Open your browser to:
```
http://localhost:8080/viewer/quick-test
```

You should see all the test messages appearing in real-time with different colors for each log level.

## Automated Test Suite

For comprehensive automated testing:

```bash
php tests/test-server.php
```

This will:
- 🚀 Automatically start a test server on port 8888
- 🧪 Run all unit and integration tests
- ✅ Report results
- 🛑 Stop the test server

Expected output:
```
╔════════════════════════════════════════════════════════════╗
║         PHPConsoleLog Server Test Suite                   ║
╚════════════════════════════════════════════════════════════╝

🔧 Starting test server on port 8888...

Running tests...
────────────────────────────────────────────────────────────

Test: Server Health Check... ✅ PASS
Test: Basic Logging... ✅ PASS
Test: All Log Levels... ✅ PASS
Test: Complex Data Types... ✅ PASS
Test: Multiple Sequential Messages... ✅ PASS
Test: Viewer Endpoint... ✅ PASS
Test: Invalid Endpoint (404)... ✅ PASS

────────────────────────────────────────────────────────────

╔════════════════════════════════════════════════════════════╗
║                     TEST RESULTS                           ║
╚════════════════════════════════════════════════════════════╝

  Total Tests:  7
  ✅ Passed:     7
  ❌ Failed:     0
  Pass Rate:    100.0%

🎉 All tests passed! Your server is ready for deployment.
```

## Manual Testing

For detailed step-by-step manual testing, see: [`tests/manual-test.md`](tests/manual-test.md)

This guide includes:
- Testing multiple keys/sessions
- AJAX example testing
- Performance testing
- Error handling verification
- Different data type testing

## Pre-Deployment Checklist

Before deploying, make sure:

- [ ] `php tests/test-server.php` passes all tests
- [ ] `php tests/quick-test.php` works correctly
- [ ] Manual viewer test works at `http://localhost:8080/viewer/test`
- [ ] No PHP warnings or errors appear in the server console
- [ ] All example files run without errors
- [ ] The viewer displays logs in real-time
- [ ] Different log levels show with correct colors
- [ ] Complex data (arrays, objects, exceptions) display properly

## Troubleshooting

### "Server not running" error

**Problem**: Quick test says server isn't running

**Solution**:
1. Make sure you started the server first: `php examples/server-start.php`
2. Wait a few seconds for server to fully start
3. Check if port 8080 is available

### Port already in use

**Problem**: "Address already in use" error

**Solution** (Windows):
```powershell
netstat -ano | findstr :8080
taskkill /PID <pid> /F
```

**Solution** (Linux/Mac):
```bash
lsof -ti :8080 | xargs kill -9
```

### Viewer loads but no logs appear

**Problem**: Viewer page loads but logs don't show up

**Checklist**:
1. Is the server still running? Check the terminal
2. Does the key match? (e.g., both use 'quick-test')
3. Check browser console for WebSocket errors (F12)
4. Try refreshing the viewer page

### cURL extension not found

**Problem**: `Call to undefined function curl_init()`

**Solution**: Install PHP cURL extension
- Windows: Enable in `php.ini`: `extension=curl`
- Linux: `sudo apt-get install php-curl`
- Mac: `brew install php` (includes curl)

## What's Being Tested?

The test suite verifies:

1. **Server Health**: Server starts and responds
2. **Basic Logging**: Simple log messages work
3. **Log Levels**: debug, info, log, warning, error all work
4. **Data Types**: Strings, numbers, booleans, nulls, arrays, objects
5. **Complex Data**: Nested structures, exceptions, resources
6. **Multiple Messages**: Rapid sequential logging
7. **HTTP Endpoints**: Viewer and logger endpoints respond correctly
8. **WebSocket**: Real-time communication works
9. **Key Isolation**: Different keys don't see each other's logs
10. **Error Handling**: Invalid requests are handled gracefully

## Need Help?

If tests fail or you encounter issues:

1. Check the server output for errors
2. Review the test output for specific failure messages
3. Try manual testing to isolate the problem
4. Ensure all dependencies are installed: `composer install`
5. Check PHP version: `php -v` (requires PHP 8.0+)

## Ready to Deploy?

Once all tests pass, you're ready to:

1. Commit your changes
2. Create a new release on GitHub
3. Tag the version
4. Update documentation if needed

Good luck! 🚀
