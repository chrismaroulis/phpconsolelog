<?php
/**
 * PHPConsoleLog Server - Simple Boilerplate
 * 
 * Copy this file to your project root and run:
 *   php server.php
 * 
 * Or use the provided scripts:
 *   Windows: start-server.bat
 *   PowerShell: .\start-server.ps1
 *   Linux/Mac: ./start-server.sh
 * 
 * Customize the configuration below as needed.
 */

require_once __DIR__ . '/vendor/autoload.php';

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

// ============================================================================
// CONFIGURATION - Edit these settings as needed
// ============================================================================

$port = $argv[1] ?? 8080;       // Server port - Default to 8080, unless overridden by command line argument
$host = $argv[2] ?? '0.0.0.0';  // Bind to all interfaces - Default to all interfaces, unless overridden by command line argument
$bufferSize = 100;              // Number of messages to keep in history

// ============================================================================
// SERVER STARTUP
// ============================================================================

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║              PHPConsoleLog Server                             ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "Starting server on http://localhost:{$port}\n";
echo "\n";
echo "Quick Start:\n";
echo "  1. Open viewer: http://localhost:{$port}/viewer/your-key\n";
echo "  2. Use in your code:\n";
echo "\n";
echo "     \$logger = new \\PHPConsoleLog\\Client\\Logger(\n";
echo "         'http://localhost:{$port}/logger',\n";
echo "         'your-key'\n";
echo "     );\n";
echo "     \$logger->log('Hello, World!');\n";
echo "\n";
echo "Press Ctrl+C to stop the server.\n";
echo "════════════════════════════════════════════════════════════════\n\n";

try {
    // Create server components
    $buffer = new LogBuffer($bufferSize);
    $handler = new LogHandler($buffer);
    $wsServer = new LogServer($handler);

    // Create event loop
    $loop = Factory::create();

    // Setup routes
    $routes = new RouteCollection();
    
    // WebSocket endpoint
    $routes->add('ws', new Route('/ws', [
        '_controller' => new WsServer($wsServer)
    ], [], [], '', [], []));
    
    // HTTP logger endpoint (POST)
    $routes->add('logger', new Route('/logger', [
        '_controller' => $handler
    ], [], [], '', [], ['POST', 'OPTIONS']));
    
    // Web viewer endpoint (GET)
    $routes->add('viewer', new Route('/viewer/{key}', [
        '_controller' => $handler
    ], [], [], '', [], ['GET']));

    // Create HTTP server with routing
    $router = new Router(
        new UrlMatcher($routes, new RequestContext())
    );
    $httpServer = new HttpServer($router);

    // Create socket server
    $socket = new SocketServer("{$host}:{$port}", $loop);
    $server = new IoServer($httpServer, $socket, $loop);

    echo "Server is running...\n\n";

    // Start the event loop
    $loop->run();

} catch (\Exception $e) {
    echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║                         ERROR                                 ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n\n";
    echo "Failed to start server: {$e->getMessage()}\n\n";
    echo "Common solutions:\n";
    echo "  • Check if port {$port} is already in use\n";
    echo "  • Try a different port: php server.php\n";
    echo "  • Make sure you have run: composer install\n";
    echo "\n";
    exit(1);
}

