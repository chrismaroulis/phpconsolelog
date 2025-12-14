<?php
/**
 * PHPConsoleLog Client Example
 * 
 * This example demonstrates how to use PHPConsoleLog in your application
 * 
 * Before running this example:
 * 1. Start the server: php examples/server-start.php
 * 2. Open the viewer in your browser: http://localhost:8080/viewer/my-app-key
 * 3. Run this script: php examples/client-example.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PHPConsoleLog\Client\Logger;

// Initialize the logger
$logger = new Logger('http://localhost:8080/logger', 'my-app-key');

echo "PHPConsoleLog Client Example\n";
echo "============================\n\n";
echo "Make sure you have:\n";
echo "1. Started the server (php examples/server-start.php)\n";
echo "2. Opened the viewer at http://localhost:8080/viewer/my-app-key\n\n";
echo "Sending log messages...\n\n";

// Basic logging
$logger->log("Hello from PHPConsoleLog!");
sleep(1);

// Multiple values
$logger->log("You can log multiple values:", 42, true, null);
sleep(1);

// Different log levels
$logger->debug("This is a debug message");
sleep(1);

$logger->info("This is an info message");
sleep(1);

$logger->warning("This is a warning message");
sleep(1);

$logger->error("This is an error message");
sleep(1);

// Arrays
$user = [
    'id' => 123,
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'roles' => ['admin', 'user']
];
$logger->log("User data:", $user);
sleep(1);

// Objects
class Product {
    public $id = 456;
    public $name = "Widget";
    public $price = 29.99;
}

$product = new Product();
$logger->log("Product object:", $product);
sleep(1);

// Nested data
$order = [
    'order_id' => 'ORD-001',
    'customer' => $user,
    'items' => [
        ['product' => 'Widget', 'quantity' => 2, 'price' => 29.99],
        ['product' => 'Gadget', 'quantity' => 1, 'price' => 49.99],
    ],
    'total' => 109.97
];
$logger->log("Order details:", $order);
sleep(1);

// Simulating an error scenario
try {
    throw new \RuntimeException("Something went wrong!");
} catch (\Exception $e) {
    $logger->error("Caught exception:", $e);
}
sleep(1);

// Loop demonstration
$logger->info("Processing items in a loop...");
for ($i = 1; $i <= 5; $i++) {
    $logger->debug("Processing item", $i);
    usleep(500000); // 0.5 seconds
}

$logger->info("Example completed! Check your viewer window.");

echo "\nAll messages sent!\n";
echo "Check your browser to see the logs in real-time.\n";

