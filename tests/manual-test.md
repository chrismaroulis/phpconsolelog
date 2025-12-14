# Manual Testing Guide for PHPConsoleLog

This guide will help you manually test all features of PHPConsoleLog before deployment.

## Prerequisites

- PHP 8.0 or higher
- Composer dependencies installed (`composer install`)
- An available port (default: 8080)

## Test 1: Start the Server

1. Open a terminal in the project root directory
2. Run the server:
   ```bash
   php examples/server-start.php
   ```

3. You should see output like:
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

**Expected Result:** ✅ Server starts without errors

## Test 2: Open the Viewer

1. Open a web browser
2. Navigate to: `http://localhost:8080/viewer/my-app-key`
3. You should see a clean console viewer interface

**Expected Result:** ✅ Viewer page loads with the PHPConsoleLog interface

## Test 3: Run the Client Example

1. Open a **second terminal** (keep the server running in the first)
2. Run the client example:
   ```bash
   php examples/client-example.php
   ```

3. Watch the viewer in your browser - you should see logs appearing in real-time:
   - "Hello from PHPConsoleLog!"
   - Multiple values logged together
   - Different colored log levels (debug, info, warning, error)
   - Arrays and objects displayed
   - Exception information

**Expected Result:** ✅ All log messages appear in the viewer in real-time

## Test 4: Test Multiple Keys

1. Keep the server running
2. Open two browser tabs:
   - Tab 1: `http://localhost:8080/viewer/key-one`
   - Tab 2: `http://localhost:8080/viewer/key-two`

3. In a terminal, create a test script `test-keys.php`:
   ```php
   <?php
   require_once __DIR__ . '/vendor/autoload.php';
   use PHPConsoleLog\Client\Logger;

   $logger1 = new Logger('http://localhost:8080/logger', 'key-one');
   $logger2 = new Logger('http://localhost:8080/logger', 'key-two');

   $logger1->log("This is for key-one");
   $logger2->log("This is for key-two");
   $logger1->info("More messages for key-one");
   $logger2->warning("Warning for key-two");
   ```

4. Run: `php test-keys.php`

**Expected Result:** ✅ Each viewer shows only its own messages

## Test 5: Test the AJAX Example

1. Keep the server running
2. Start PHP's built-in web server in another terminal:
   ```bash
   php -S localhost:8000 -t examples
   ```

3. Open your browser to: `http://localhost:8000/ajax-example.php`
4. Open the viewer in another tab: `http://localhost:8080/viewer/ajax-debug`
5. Click the buttons on the AJAX page
6. Watch the viewer show the debug information

**Expected Result:** ✅ AJAX requests are logged without interfering with JSON responses

## Test 6: Test Server Restart

1. Stop the server (Ctrl+C)
2. Start it again
3. The server should start cleanly

**Expected Result:** ✅ Server can be stopped and restarted without issues

## Test 7: Test Different Ports

1. Stop the server
2. Start it on a different port:
   ```bash
   php examples/server-start.php 9090
   ```

3. Update your test script to use port 9090
4. Verify everything still works

**Expected Result:** ✅ Server works on custom ports

## Test 8: Test Error Handling

1. Keep the server running
2. Create a test script to send invalid data:
   ```php
   <?php
   $ch = curl_init('http://localhost:8080/logger');
   curl_setopt($ch, CURLOPT_POST, true);
   curl_setopt($ch, CURLOPT_POSTFIELDS, 'invalid json');
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   $response = curl_exec($ch);
   $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
   // curl_close($ch);
   
   echo "HTTP Code: {$httpCode}\n";
   echo "Response: {$response}\n";
   ```

**Expected Result:** ✅ Server returns 400 Bad Request for invalid data

## Test 9: Test Performance

1. Create a script to send many messages quickly:
   ```php
   <?php
   require_once __DIR__ . '/vendor/autoload.php';
   use PHPConsoleLog\Client\Logger;

   $logger = new Logger('http://localhost:8080/logger', 'performance-test');
   
   $start = microtime(true);
   for ($i = 0; $i < 100; $i++) {
       $logger->log("Message #{$i}");
   }
   $end = microtime(true);
   
   echo "Sent 100 messages in " . round($end - $start, 2) . " seconds\n";
   ```

**Expected Result:** ✅ All messages are sent and received without errors

## Test 10: Test Different Data Types

Create a comprehensive test:
```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
use PHPConsoleLog\Client\Logger;

$logger = new Logger('http://localhost:8080/logger', 'data-types');

// Strings
$logger->log("Simple string");

// Numbers
$logger->log(42, 3.14159, -100, 0);

// Booleans
$logger->log(true, false);

// Null
$logger->log(null);

// Arrays
$logger->log([1, 2, 3, 4, 5]);
$logger->log(['name' => 'John', 'age' => 30]);

// Nested arrays
$logger->log([
    'user' => ['id' => 1, 'name' => 'John'],
    'posts' => [
        ['id' => 1, 'title' => 'First'],
        ['id' => 2, 'title' => 'Second']
    ]
]);

// Objects
class TestClass {
    public $public = 'public value';
    private $private = 'private value';
    protected $protected = 'protected value';
}
$logger->log(new TestClass());

// Exceptions
try {
    throw new RuntimeException("Test error", 123);
} catch (Exception $e) {
    $logger->error($e);
}

// Resources
$file = fopen(__FILE__, 'r');
$logger->log($file);
fclose($file);
```

**Expected Result:** ✅ All data types are properly formatted and displayed

## Checklist Before Deployment

- [ ] Server starts without errors
- [ ] Viewer page loads correctly
- [ ] Client can send log messages
- [ ] Real-time updates work in viewer
- [ ] Multiple keys are isolated correctly
- [ ] All log levels display with correct styling
- [ ] Complex data structures display properly
- [ ] Error handling works correctly
- [ ] Server can be stopped and restarted
- [ ] No PHP warnings or errors
- [ ] AJAX example works without interfering with responses

## Troubleshooting

### Port Already in Use
If you get a "port already in use" error:
- **Windows**: `netstat -ano | findstr :8080` then `taskkill /PID <pid> /F`
- **Linux/Mac**: `lsof -ti :8080 | xargs kill -9`

### Connection Refused
- Make sure the server is running
- Check that your firewall allows the connection
- Try using `127.0.0.1` instead of `localhost`

### Logs Not Appearing
- Check the browser console for WebSocket errors
- Verify the key matches between logger and viewer
- Ensure the server hasn't crashed (check terminal)

### Dependencies Missing
Run: `composer install`

## Automated Testing

For automated testing, use the test suite:
```bash
php tests/test-server.php
```

This will automatically start the server, run all tests, and stop the server.
