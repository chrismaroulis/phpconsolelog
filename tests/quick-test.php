<?php
/**
 * Quick Test Script
 * 
 * A simple script to quickly verify the server is working
 * 
 * Usage:
 * 1. Start server in one terminal: php examples/server-start.php
 * 2. Run this in another terminal: php tests/quick-test.php
 * 3. Open http://localhost:8080/viewer/quick-test in your browser
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PHPConsoleLog\Client\Logger;

// Check if server is running
$ch = curl_init('http://localhost:8080/viewer/quick-test');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 2);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "❌ Server not running!\n";
    echo "Please start the server first: php examples/server-start.php\n";
    exit(1);
}

echo "✅ Server is running\n";
echo "📺 Open viewer: http://localhost:8080/viewer/quick-test\n\n";
echo "Sending test messages...\n\n";

$logger = new Logger('http://localhost:8080/logger', 'quick-test');

// Send test messages with delays so you can see them
$logger->log("🚀 Quick Test Started!");
sleep(1);

$logger->debug("Debug level message");
sleep(1);

$logger->info("Info level message");
sleep(1);

$logger->warning("Warning level message");
sleep(1);

$logger->error("Error level message");
sleep(1);

$logger->log("Testing array:", ['key' => 'value', 'number' => 42]);
sleep(1);

$logger->log("Testing object:", (object)['prop' => 'test', 'value' => 123]);
sleep(1);

$logger->log("Multiple values:", "text", 123, true, null);
sleep(1);

try {
    throw new Exception("Test exception");
} catch (Exception $e) {
    $logger->error("Exception test:", $e);
}
sleep(1);

$logger->log("✅ Quick Test Complete!");

echo "\n✨ All test messages sent successfully!\n";
echo "Check your browser to see the logs.\n";
