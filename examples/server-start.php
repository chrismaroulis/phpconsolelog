<?php
/**
 * PHPConsoleLog Server Starter
 * 
 * Start the WebSocket server for receiving and broadcasting log messages
 * 
 * Usage: php examples/server-start.php [port] [host]
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PHPConsoleLog\Server\LogBuffer;
use PHPConsoleLog\Server\LogHandler;
use PHPConsoleLog\Server\LogServer;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\App;

// Configuration
$port = $argv[1] ?? 8080;
$host = $argv[2] ?? '0.0.0.0';

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║              PHPConsoleLog Server                             ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "Starting server on {$host}:{$port}\n";
echo "\n";
echo "Endpoints:\n";
echo "  • POST http://{$host}:{$port}/logger  - Log messages endpoint\n";
echo "  • GET  http://{$host}:{$port}/viewer/{key} - Web viewer\n";
echo "  • WS   ws://{$host}:{$port}/ws        - WebSocket endpoint\n";
echo "\n";
echo "Example usage:\n";
echo "  \$logger = new \\PHPConsoleLog\\Client\\Logger('http://{$host}:{$port}/logger', 'your-key');\n";
echo "  \$logger->log('Hello, World!');\n";
echo "\n";
echo "View logs at: http://{$host}:{$port}/viewer/your-key\n";
echo "\n";
echo "Press Ctrl+C to stop the server.\n";
echo "════════════════════════════════════════════════════════════════\n\n";

try {
    // Create components
    $buffer = new LogBuffer(100);
    $handler = new LogHandler($buffer);
    $wsServer = new LogServer($handler);

    // Create Ratchet application
    $app = new App($host, $port, '0.0.0.0');

    // WebSocket route for real-time communication
    $app->route('/ws', $wsServer, ['*']);

    // HTTP route for logging endpoint and viewer
    $app->route('/logger', $handler, ['*']);
    $app->route('/viewer', $handler, ['*']);

    echo "Server is running...\n\n";

    // Run the server
    $app->run();

} catch (\Exception $e) {
    echo "\nError starting server: {$e->getMessage()}\n";
    echo "Make sure the port is not already in use.\n";
    exit(1);
}

