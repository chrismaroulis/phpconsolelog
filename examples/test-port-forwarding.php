<?php
/**
 * Port Forwarding Test Script
 * 
 * This script simulates requests coming through different hosts/ports
 * to verify that WebSocket URL generation works correctly
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PHPConsoleLog\Server\LogHandler;
use PHPConsoleLog\Server\LogBuffer;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;

$exampleDomain = 'example.com';
$examplePort = 8181;

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║        Port Forwarding / WebSocket URL Test                   ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Create handler
$buffer = new LogBuffer(100);
$handler = new LogHandler($buffer);

// Use reflection to access private method for testing
$reflection = new ReflectionClass($handler);
$method = $reflection->getMethod('constructWebSocketUrl');
$method->setAccessible(true);

// Test scenarios
$scenarios = [
    [
        'name' => 'Internal LAN access (direct)',
        'host' => '192.168.1.11:8080',
        'headers' => [],
        'expected' => 'ws://192.168.1.11:8080/ws'
    ],
    [
        'name' => 'External access through port forwarding',
        'host' => $exampleDomain . ':' . $examplePort,
        'headers' => [],
        'expected' => 'ws://' . $exampleDomain . ':' . $examplePort . '/ws'
    ],
    [
        'name' => 'Standard HTTP port (should omit port)',
        'host' => $exampleDomain,
        'headers' => [],
        'expected' => 'ws://' . $exampleDomain . '/ws'
    ],
    [
        'name' => 'Behind reverse proxy with X-Forwarded headers',
        'host' => 'localhost:8080',
        'headers' => [
            'X-Forwarded-Host' => [$exampleDomain],
            'X-Forwarded-Port' => ['8181'],
            'X-Forwarded-Proto' => ['http']
        ],
        'expected' => 'ws://' . $exampleDomain . ':' . $examplePort . '/ws'
    ],
    [
        'name' => 'HTTPS through reverse proxy (should use wss)',
        'host' => 'localhost:8080',
        'headers' => [
            'X-Forwarded-Host' => [$exampleDomain],
            'X-Forwarded-Port' => ['443'],
            'X-Forwarded-Proto' => ['https']
        ],
        'expected' => 'wss://' . $exampleDomain . '/ws'
    ],
    [
        'name' => 'HTTPS with custom port',
        'host' => 'localhost:8080',
        'headers' => [
            'X-Forwarded-Host' => [$exampleDomain],
            'X-Forwarded-Port' => ['8443'],
            'X-Forwarded-Proto' => ['https']
        ],
        'expected' => 'wss://' . $exampleDomain . ':8443/ws'
    ],
];

$passed = 0;
$failed = 0;

foreach ($scenarios as $i => $scenario) {
    echo sprintf("Test %d: %s\n", $i + 1, $scenario['name']);
    echo "  Input:    {$scenario['host']}\n";
    
    // Create mock request
    $uri = new Uri("http://{$scenario['host']}/viewer/test-key");
    $headers = array_merge(['Host' => [$scenario['host']]], $scenario['headers']);
    
    $request = new Request('GET', $uri, $headers);
    
    // Test the method
    try {
        $result = $method->invoke($handler, $request);
        
        if ($result === $scenario['expected']) {
            echo "  Result:   \033[32m✓ {$result}\033[0m\n";
            echo "  Expected: {$scenario['expected']}\n";
            $passed++;
        } else {
            echo "  Result:   \033[31m✗ {$result}\033[0m\n";
            echo "  Expected: {$scenario['expected']}\n";
            $failed++;
        }
    } catch (Exception $e) {
        echo "  Result:   \033[31m✗ ERROR: {$e->getMessage()}\033[0m\n";
        $failed++;
    }
    
    echo "\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo sprintf("Results: %d passed, %d failed\n", $passed, $failed);

if ($failed === 0) {
    echo "\033[32m✓ All tests passed! Port forwarding will work correctly.\033[0m\n";
} else {
    echo "\033[31m✗ Some tests failed. Please check the implementation.\033[0m\n";
}

echo "\n";
echo "Your scenario test:\n";
echo "  Server internal: 192.168.1.11:8080\n";
echo "  External domain: " . $exampleDomain . ':' . $examplePort . "\n";
echo "\n";
echo "When clients access http://" . $exampleDomain . ':' . $examplePort . '/viewer/my-key:\n';
echo "  ✓ HTTP POST to /logger will work\n";
echo "  ✓ WebSocket will connect to ws://" . $exampleDomain . ':' . $examplePort . '/ws\n';
echo "  ✓ Router forwards traffic to 192.168.1.11:8080\n";
echo "\n";
echo "Setup instructions:\n";
echo "  1. Start server: php examples/server-start.php 8080 0.0.0.0\n";
echo "  2. Configure router to forward port 8181 → 192.168.1.11:8080\n";
echo "  3. Clients use: http://" . $exampleDomain . ':' . $examplePort . "\n";
echo "\n";
