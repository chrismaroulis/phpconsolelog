# PHPConsoleLog - Quick Start Guide

Get up and running with PHPConsoleLog in 5 minutes!

## Prerequisites

Make sure you have:
- ✅ PHP 7.4 or higher installed
- ✅ Composer installed
- ✅ A terminal/command prompt
- ✅ A web browser

## Step 1: Install Dependencies

Open your terminal in the project directory and run:

```bash
composer install
```

This will download and install:
- Ratchet (WebSocket library)
- Guzzle (HTTP client)
- Other dependencies

## Step 2: Start the Server

**Option A: Using the simple boilerplate (recommended)**

First, copy the server file to your project root:

```bash
cp vendor/phpconsolelog/phpconsolelog/server.php .
```

Then start it with:

```bash
# Windows
start-server.bat

# PowerShell
.\start-server.ps1

# Linux/Mac
./start-server.sh

# Or directly
php server.php
```

**Option B: Using the examples directory**

```bash
php examples/server-start.php
```

You should see:

```
╔═══════════════════════════════════════════════════════════════╗
║              PHPConsoleLog Server                             ║
╚═══════════════════════════════════════════════════════════════╝

Starting server on 0.0.0.0:8080

Endpoints:
  • POST http://0.0.0.0:8080/logger  - Log messages endpoint
  • GET  http://0.0.0.0:8080/viewer/{key} - Web viewer
  • WS   ws://0.0.0.0:8080/ws        - WebSocket endpoint

Server is running...
```

✅ Leave this terminal window open! The server needs to keep running.

## Step 3: Open the Viewer

Open your web browser and navigate to:

```
http://localhost:8080/viewer/test-key
```

You should see a dark console interface with "Waiting for log messages..." 

✅ Keep this browser window open!

## Step 4: Run the Example

Open a **new terminal window** (keep the server running in the first one) and run:

```bash
php examples/client-example.php
```

🎉 **Watch the magic happen!** You should see logs appearing in real-time in your browser window!

## Step 5: Try It in Your Own Code

Create a new PHP file called `my-test.php`:

```php
<?php
require_once 'vendor/autoload.php';

use PHPConsoleLog\Client\Logger;

// Create logger
$logger = new Logger('http://localhost:8080/logger', 'test-key');

// Log some messages
$logger->log("Hello from my code!");
$logger->info("This is so cool!");

$data = ['name' => 'John', 'age' => 30];
$logger->log("Here's some data:", $data);

$logger->warning("This is a warning");
$logger->error("This is an error");

echo "Check your browser! Logs should appear there.\n";
```

Run it:

```bash
php my-test.php
```

Watch the logs appear in your browser! 🚀

## What Just Happened?

1. **Server** - The WebSocket server is listening on port 8080
2. **Viewer** - Your browser connected to the server using the key "test-key"
3. **Client** - Your PHP script sent log messages to the server
4. **WebSocket** - The server instantly pushed those logs to your browser

## Debugging AJAX Requests

Want to debug AJAX without breaking your JSON responses? Try the AJAX example:

### Terminal 1 (keep server running):
```bash
php examples/server-start.php
```

### Terminal 2 (start a web server):
```bash
php -S localhost:8000 examples/ajax-example.php
```

### Browser Window 1 (log viewer):
Open: http://localhost:8080/viewer/ajax-debug

### Browser Window 2 (test page):
Open: http://localhost:8000

Click the buttons and watch the logs appear in the viewer window!

## Using Different Keys

You can have multiple log streams by using different keys:

```php
$authLogger = new Logger('http://localhost:8080/logger', 'auth');
$dbLogger = new Logger('http://localhost:8080/logger', 'database');
$apiLogger = new Logger('http://localhost:8080/logger', 'api');
```

Then open multiple viewer windows:
- http://localhost:8080/viewer/auth
- http://localhost:8080/viewer/database  
- http://localhost:8080/viewer/api

Each viewer will only show logs from its corresponding key!

## Troubleshooting

### "Connection Refused"

**Problem:** Client can't connect to server  
**Solution:** Make sure the server is running in another terminal

### "Port Already in Use"

**Problem:** Port 8080 is being used by another application  
**Solution:** Start the server on a different port:

```bash
php examples/server-start.php 9000
```

Then update your client:
```php
$logger = new Logger('http://localhost:9000/logger', 'test-key');
```

And viewer: http://localhost:9000/viewer/test-key

### Logs Not Appearing

**Checklist:**
- ✅ Is the server running?
- ✅ Is the same key used in both client and viewer?
- ✅ Check browser console (F12) for WebSocket errors
- ✅ Is the server URL correct in your client code?

## Next Steps

Now that you're up and running:

1. **Read the full [README.md](README.md)** for detailed documentation
2. **Explore more [examples/](examples/)** for different use cases
3. **Integrate into your project** and debug with ease!
4. **Check out [CONTRIBUTING.md](CONTRIBUTING.md)** if you want to contribute

## Tips & Tricks

### 💡 Tip 1: Auto-scroll
The viewer automatically scrolls to show new logs. Scroll up to view history, and it will auto-scroll again once you reach the bottom.

### 💡 Tip 2: Clear Console
Click the "Clear Console" button to reset the view.

### 💡 Tip 3: Production Safety
Remember to disable logging in production:

```php
$logger = new Logger('http://localhost:8080/logger', 'my-key');

if ($_ENV['APP_ENV'] === 'production') {
    $logger->disable();
}
```

### 💡 Tip 4: Multiple Viewers
Multiple people can watch the same log stream by opening the viewer with the same key!

---

**Happy Debugging!** 🐛✨

Need help? Check the [README.md](README.md) or open an issue on GitHub.

