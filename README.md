# PHPConsoleLog

Real-time console logging for PHP applications with WebSocket support. Debug your PHP code with the same ease as using `console.log()` in JavaScript!

> **Disclaimer:** This entire repository, including all code and implementation, was initially created with Cursor and Claude Sonnet 4.5. Only the prompt was made by Chris Maroulis.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-blue.svg)](https://php.net)

## Features

✨ **Real-time Streaming** - See your logs instantly in a browser window via WebSockets  
🎨 **Color-Coded Levels** - Debug, Info, Warning, and Error with visual distinction  
📦 **Multiple Data Types** - Log strings, arrays, objects, exceptions, and more  
🔄 **Non-Blocking** - Async log sending won't slow down your application  
🌐 **Framework Agnostic** - Works with any PHP application or framework  
👥 **Multiple Viewers** - Several developers can watch the same log stream  
💾 **Buffered History** - New viewers see the last 100 messages immediately  
🧹 **Clear Console** - Reset the log view with one click  

## Why PHPConsoleLog?

Traditional PHP debugging often involves:
- Using `var_dump()` or `print_r()` which breaks your application's output
- Tailing log files with `tail -f`
- Installing heavy debugging tools like Xdebug
- Breaking AJAX responses with debug output

PHPConsoleLog provides a lightweight, real-time alternative that doesn't interfere with your application's output.

## Installation

Install via Composer:

```bash
composer require phpconsolelog/phpconsolelog
```

### Quick Deployment

After installation, copy the boilerplate files to your project root:

```bash
# Copy the server file
cp vendor/phpconsolelog/phpconsolelog/server.php .

# Copy the launcher scripts (choose what you need)
cp vendor/phpconsolelog/phpconsolelog/start-server.bat .      # Windows
cp vendor/phpconsolelog/phpconsolelog/start-server.ps1 .      # PowerShell
cp vendor/phpconsolelog/phpconsolelog/start-server.sh .       # Linux/Mac

# Make the shell script executable (Linux/Mac)
chmod +x start-server.sh
```

That's it! Now you can start the server with a simple command.

## Quick Start

### 1. Start the Server

**Option A: Use the simple boilerplate (recommended for users)**

Copy `server.php` to your project and run one of these commands:

```bash
# Windows (Command Prompt)
start-server.bat

# Windows (PowerShell)
.\start-server.ps1

# Linux/Mac
./start-server.sh

# Or directly with PHP (all platforms)
php server.php
```

**Option B: Run from vendor directory (for development/testing)**

```bash
php vendor/phpconsolelog/phpconsolelog/examples/server-start.php
```

The server will start on `http://localhost:8080` by default.

### 2. Open the Viewer

Open your browser and navigate to:

```
http://localhost:8080/viewer/your-unique-key
```

Replace `your-unique-key` with any identifier for your logging session (e.g., your project name).

### 3. Use in Your Application

```php
<?php
require_once 'vendor/autoload.php';

use PHPConsoleLog\Client\Logger;

// Initialize the logger
$logger = new Logger('http://localhost:8080/logger', 'your-unique-key');

// Start logging!
$logger->log("Hello, World!");
$logger->info("Application started");
$logger->warning("Low memory warning");
$logger->error("Failed to connect to database");
```

Watch your logs appear instantly in the browser!

## Usage Examples

### Basic Logging

```php
// Simple messages
$logger->log("User logged in");

// Multiple values
$logger->log("User ID:", $userId, "Session:", $sessionId);

// Different log levels
$logger->debug("Debug information");
$logger->info("Informational message");
$logger->warning("Warning message");
$logger->error("Error message");
```

### Logging Complex Data

```php
// Arrays
$user = [
    'id' => 123,
    'name' => 'John Doe',
    'email' => 'john@example.com'
];
$logger->log("User data:", $user);

// Objects
$product = new Product();
$logger->log("Product:", $product);

// Exceptions
try {
    throw new Exception("Something went wrong");
} catch (Exception $e) {
    $logger->error("Caught exception:", $e);
}
```

### AJAX Request Debugging

Perfect for debugging API endpoints without breaking JSON responses:

```php
<?php
use PHPConsoleLog\Client\Logger;

$logger = new Logger('http://localhost:8080/logger', 'api-debug');

// Your API logic
$logger->log("API request received:", $_POST);

$result = processRequest($_POST);

$logger->log("API response:", $result);

// Return JSON (logging doesn't interfere!)
header('Content-Type: application/json');
echo json_encode($result);
```

### Background Job Monitoring

Watch long-running scripts in real-time:

```php
<?php
use PHPConsoleLog\Client\Logger;

$logger = new Logger('http://localhost:8080/logger', 'batch-job');

$logger->info("Starting batch job");

foreach ($items as $item) {
    $logger->log("Processing item:", $item['id']);
    processItem($item);
}

$logger->info("Batch job completed");
```

## API Reference

### Client Logger

#### Constructor

```php
new Logger(string $serverUrl, string $key, array $options = [])
```

**Parameters:**
- `$serverUrl` - URL of the logging server endpoint
- `$key` - Unique identifier for your logging session
- `$options` - Optional configuration array
  - `timeout` - HTTP timeout in seconds (default: 1.0)
  - `async` - Use async requests (default: true)
  - `debug` - Log errors to PHP error_log (default: false)

#### Methods

```php
$logger->debug(...$data): void      // Log debug message
$logger->info(...$data): void       // Log info message
$logger->log(...$data): void        // Alias for info()
$logger->warning(...$data): void    // Log warning message
$logger->error(...$data): void      // Log error message

$logger->enable(): void             // Enable logging
$logger->disable(): void            // Disable logging
$logger->isEnabled(): bool          // Check if enabled
```

All logging methods accept variadic parameters, so you can pass multiple values:

```php
$logger->log("User:", $user, "Action:", $action, "Result:", $result);
```

## Server Configuration

### Starting the Server

**Using the boilerplate files (recommended):**

The package includes `server.php` and launch scripts that you can copy to your project:

```bash
# Windows Batch
start-server.bat

# Windows PowerShell  
.\start-server.ps1

# Linux/Mac Bash
./start-server.sh

# Direct PHP
php server.php
```

To customize the port or host, edit the configuration section at the top of `server.php`:

```php
// CONFIGURATION - Edit these settings as needed
$port = 8080;                    // Server port
$host = '0.0.0.0';              // Bind to all interfaces
$bufferSize = 100;              // Number of messages to keep in history
```

**Using the examples directory:**

```bash
# Default (port 8080, all interfaces)
php vendor/phpconsolelog/phpconsolelog/examples/server-start.php

# Custom port
php vendor/phpconsolelog/phpconsolelog/examples/server-start.php 9000

# Custom host and port
php vendor/phpconsolelog/phpconsolelog/examples/server-start.php 9000 127.0.0.1
```

### Server Endpoints

- `POST /logger` - Receive log messages from client applications
- `GET /viewer/{key}` - Serve the web viewer for a specific key
- `WS /ws` - WebSocket endpoint for real-time communication

### Buffer Configuration

By default, the server keeps the last 100 messages per key in memory. You can customize this in your own server implementation:

```php
<?php
use PHPConsoleLog\Server\LogBuffer;

$buffer = new LogBuffer(200); // Keep last 200 messages
```

## Advanced Usage

### Custom Server Implementation

If you need more control, you can create your own server script:

```php
<?php
require_once 'vendor/autoload.php';

use PHPConsoleLog\Server\LogBuffer;
use PHPConsoleLog\Server\LogHandler;
use PHPConsoleLog\Server\LogServer;
use Ratchet\App;

$buffer = new LogBuffer(100);
$handler = new LogHandler($buffer);
$wsServer = new LogServer($handler);

$app = new App('localhost', 8080);
$app->route('/ws', $wsServer, ['*']);
$app->route('/logger', $handler, ['*']);
$app->route('/viewer', $handler, ['*']);

$app->run();
```

### Conditional Logging

Enable/disable logging based on environment:

```php
<?php
$logger = new Logger('http://localhost:8080/logger', 'my-key');

// Only log in development
if (getenv('APP_ENV') !== 'production') {
    $logger->enable();
} else {
    $logger->disable();
}
```

### Multiple Log Streams

Use different keys for different parts of your application:

```php
<?php
$authLogger = new Logger('http://localhost:8080/logger', 'auth');
$apiLogger = new Logger('http://localhost:8080/logger', 'api');
$dbLogger = new Logger('http://localhost:8080/logger', 'database');

$authLogger->log("User authentication attempt");
$apiLogger->log("API request received");
$dbLogger->log("Database query executed");
```

Then open multiple viewer windows:
- http://localhost:8080/viewer/auth
- http://localhost:8080/viewer/api
- http://localhost:8080/viewer/database

## Security Considerations

⚠️ **Important Security Notes:**

1. **Development Only** - PHPConsoleLog is intended for development and debugging. Do not use in production environments.

2. **No Built-in Authentication** - The viewer pages are publicly accessible if someone knows the key. Use sufficiently random keys.

3. **Network Access** - If running on a server, ensure the port is not exposed to the public internet.

4. **Sensitive Data** - Be careful not to log sensitive information (passwords, API keys, etc.).

5. **HTTPS** - For sensitive applications, consider running behind a reverse proxy with HTTPS.

## Requirements

- PHP 7.4 or higher
- Composer
- [Ratchet](http://socketo.me/) WebSocket library
- [Guzzle](https://docs.guzzlephp.org/) HTTP client

## Troubleshooting

### Logs Not Appearing

1. **Check server is running:** Make sure `php examples/server-start.php` is running
2. **Check the key:** Ensure you're using the same key in both the client and viewer
3. **Check the URL:** Verify the server URL is correct in your Logger constructor
4. **Check browser console:** Open browser dev tools to see WebSocket connection status

### Port Already in Use

If port 8080 is already in use, start the server on a different port:

```bash
php examples/server-start.php 9000
```

Then update your client to use the new port:

```php
$logger = new Logger('http://localhost:9000/logger', 'my-key');
```

### Connection Refused

If the client can't connect to the server:
- Ensure no firewall is blocking the port
- Check that the server is accessible from your client's location
- Try using `127.0.0.1` instead of `localhost`

## Examples

The `examples/` directory contains several working examples:

- **`client-example.php`** - Basic usage demonstration
- **`ajax-example.php`** - AJAX request debugging
- **`server-start.php`** - Server startup script

Run any example:

```bash
php examples/client-example.php
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

- Built with [Ratchet](http://socketo.me/) WebSocket library
- Inspired by browser console debugging and tools like [Ray](https://myray.app/)

## Support

If you encounter any issues or have questions:

1. Check the [Troubleshooting](#troubleshooting) section
2. Search existing [GitHub Issues](https://github.com/phpconsolelog/phpconsolelog/issues)
3. Create a new issue with detailed information

## Roadmap

Future enhancements being considered:

- [ ] Persistent storage option (SQLite/MySQL)
- [ ] Authentication and access control
- [ ] Log filtering and search in viewer
- [ ] Export logs to file
- [ ] Performance metrics and timing
- [ ] Stack trace visualization
- [ ] Laravel/Symfony integration packages
- [ ] Docker container for easy deployment

---

**Happy Debugging!** 🐛✨

