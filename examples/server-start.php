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
use Ratchet\Http\HttpServer;
use Ratchet\Http\Router;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use React\Socket\Server as SocketServer;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

// Configuration
$port = $argv[1] ?? 8080;
$host = $argv[2] ?? '0.0.0.0';

// Convert hostname to IP for socket binding (React requires IP, not hostname)
$bindHost = $host;
if ($host === 'localhost') {
    $bindHost = '127.0.0.1';
} elseif (!filter_var($host, FILTER_VALIDATE_IP)) {
    // If it's a hostname (not an IP), try to resolve it
    $resolved = gethostbyname($host);
    if ($resolved !== $host) {
        $bindHost = $resolved;
    } else {
        echo "Warning: Could not resolve hostname '$host', using 0.0.0.0\n";
        $bindHost = '0.0.0.0';
    }
}

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║              PHPConsoleLog Server                             ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "Starting server on {$host}:{$port}\n";
echo "Binding to: {$bindHost}:{$port}\n";
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

    // Create event loop
    $loop = Factory::create();

    // Create routes with proper HTTP method support
    $routes = new RouteCollection();
    
    // WebSocket route
    $routes->add('ws', new Route('/ws', [
        '_controller' => new WsServer($wsServer)
    ], [], [], '', [], [])); // Empty array = all methods
    
    // HTTP Logger route - accepts POST
    $routes->add('logger', new Route('/logger', [
        '_controller' => $handler
    ], [], [], '', [], ['POST', 'OPTIONS'])); // Explicitly allow POST
    
    // HTTP Viewer route - accepts GET
    $routes->add('viewer', new Route('/viewer/{key}', [
        '_controller' => $handler
    ], [], [], '', [], ['GET']));

    // Create router with HTTP server wrapper
    $router = new Router(
        new UrlMatcher($routes, new RequestContext())
    );

    $httpServer = new HttpServer($router);

    // Create socket server with IP address (not hostname)
    $socket = new SocketServer("{$bindHost}:{$port}", $loop);
    $server = new IoServer($httpServer, $socket, $loop);

    echo "Server is running...\n\n";

    // Run the event loop
    $loop->run();

} catch (\Exception $e) {
    echo "\nError starting server: {$e->getMessage()}\n";
    echo "Stack trace:\n{$e->getTraceAsString()}\n";
    echo "Make sure the port is not already in use.\n";
    exit(1);
}
