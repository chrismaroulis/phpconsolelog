<?php
/**
 * Debug Connection Test
 * 
 * This script helps diagnose connection issues between client and server
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║          PHPConsoleLog Connection Debug Tool                 ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

$serverUrl = 'http://localhost:8080/logger';
$key = 'debug-test-key';

echo "Step 1: Testing basic connectivity to server...\n";
echo "Target: $serverUrl\n\n";

// Test 1: Basic socket connection
echo "Test 1: Socket connection to localhost:8080\n";
$socket = @fsockopen('localhost', 8080, $errno, $errstr, 5);
if ($socket) {
    echo "✓ Socket connection successful\n";
    fclose($socket);
} else {
    echo "✗ Socket connection failed: $errstr ($errno)\n";
    echo "  Make sure the server is running!\n";
    exit(1);
}

echo "\n";

// Test 2: Using Guzzle with debug enabled
echo "Test 2: Testing HTTP POST with Guzzle (with full debug)...\n";

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

$client = new Client([
    'timeout' => 5.0,
    'http_errors' => false,
    'debug' => true, // This will show detailed HTTP traffic
]);

$payload = [
    'key' => $key,
    'level' => 'info',
    'data' => ['Debug test message'],
    'timestamp' => time(),
];

echo "\nSending payload:\n";
echo json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

try {
    $response = $client->post($serverUrl, [
        'json' => $payload,
        'headers' => [
            'Content-Type' => 'application/json',
        ],
    ]);
    
    echo "\n✓ Request sent successfully!\n";
    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Body: " . $response->getBody() . "\n";
    
    if ($response->getStatusCode() == 200) {
        echo "\n✓ Server accepted the log message!\n";
        echo "\nNow check:\n";
        echo "1. Server console - should show: Method: POST  Path: /logger\n";
        echo "2. Browser viewer at: http://localhost:8080/viewer/$key\n";
    } else {
        echo "\n✗ Unexpected response status\n";
    }
    
} catch (GuzzleException $e) {
    echo "\n✗ Request failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    
    if ($e instanceof \GuzzleHttp\Exception\ConnectException) {
        echo "\nConnection Error Details:\n";
        echo "- The server might not be running\n";
        echo "- Windows Firewall might be blocking the connection\n";
        echo "- The port 8080 might be used by another application\n";
    }
}

echo "\n";

// Test 3: Using PHPConsoleLog Logger with debug enabled
echo "Test 3: Testing with PHPConsoleLog Logger class...\n";

use PHPConsoleLog\Client\Logger;

$logger = new Logger($serverUrl, $key, ['debug' => true]);
$logger->log("Test message from Logger class");

echo "\n";
echo "If you see 'PHPConsoleLog: Sending log data...' above, the client is working.\n";
echo "If you see 'PHPConsoleLog: Failed to send log...' there's a connection problem.\n";
echo "\n";

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                    Debug Complete                             ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
