<?php

namespace PHPConsoleLog\Server;

use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServerInterface;
use Psr\Http\Message\RequestInterface;

/**
 * HTTP endpoint handler for log messages
 * 
 * Handles POST requests from client loggers and broadcasts to WebSocket viewers
 */
class LogHandler implements HttpServerInterface
{
    private LogBuffer $buffer;
    private array $connections = [];

    /**
     * Create a new LogHandler instance
     *
     * @param LogBuffer $buffer Log buffer instance
     */
    public function __construct(LogBuffer $buffer)
    {
        $this->buffer = $buffer;
    }

    /**
     * Handle incoming HTTP request
     *
     * @param ConnectionInterface $conn
     * @param RequestInterface $request
     * @return void
     */
    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null)
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        if ($method === 'POST' && $path === '/logger') {
            $this->handleLogRequest($conn, $request);
        } elseif ($method === 'GET' && preg_match('#^/viewer/(.+)$#', $path, $matches)) {
            $key = $matches[1];
            $this->handleViewerRequest($conn, $key);
        } else {
            $this->sendResponse($conn, 404, 'Not Found');
        }
    }

    /**
     * Handle POST /logger request
     *
     * @param ConnectionInterface $conn
     * @param RequestInterface $request
     * @return void
     */
    private function handleLogRequest(ConnectionInterface $conn, RequestInterface $request): void
    {
        $body = (string)$request->getBody();
        $data = json_decode($body, true);

        if (!$data || !isset($data['key'], $data['level'], $data['data'])) {
            $this->sendResponse($conn, 400, 'Bad Request', ['error' => 'Invalid payload']);
            return;
        }

        $key = $data['key'];
        $message = [
            'type' => 'log',
            'level' => $data['level'],
            'data' => $data['data'],
            'timestamp' => $data['timestamp'] ?? time(),
            'formatted' => $this->formatData($data['data']),
        ];

        // Store in buffer
        $this->buffer->add($key, $message);

        // Broadcast to all connected viewers for this key
        $this->broadcastToKey($key, $message);

        $this->sendResponse($conn, 200, 'OK', ['success' => true]);
    }

    /**
     * Handle GET /viewer/{key} request
     *
     * @param ConnectionInterface $conn
     * @param string $key
     * @return void
     */
    private function handleViewerRequest(ConnectionInterface $conn, string $key): void
    {
        $html = $this->getViewerHtml($key);
        $this->sendHtmlResponse($conn, 200, $html);
    }

    /**
     * Format data for display
     *
     * @param array $data
     * @return string
     */
    private function formatData(array $data): string
    {
        $formatted = [];

        foreach ($data as $item) {
            $formatted[] = $this->formatItem($item);
        }

        return implode(' ', $formatted);
    }

    /**
     * Format a single item
     *
     * @param mixed $item
     * @return string
     */
    private function formatItem($item): string
    {
        if (is_array($item)) {
            if (isset($item['_type'])) {
                switch ($item['_type']) {
                    case 'exception':
                        return sprintf(
                            "%s: %s in %s:%d",
                            $item['class'],
                            $item['message'],
                            $item['file'],
                            $item['line']
                        );
                    case 'object':
                        if (isset($item['value'])) {
                            return sprintf("%s: %s", $item['class'], $item['value']);
                        }
                        return sprintf("%s %s", $item['class'], json_encode($item['properties']));
                    case 'resource':
                        return sprintf("Resource(%s)", $item['type']);
                }
            }
            return json_encode($item);
        }

        if (is_bool($item)) {
            return $item ? 'true' : 'false';
        }

        if (is_null($item)) {
            return 'null';
        }

        return (string)$item;
    }

    /**
     * Broadcast message to all viewers with matching key
     *
     * @param string $key
     * @param array $message
     * @return void
     */
    public function broadcastToKey(string $key, array $message): void
    {
        if (!isset($this->connections[$key])) {
            return;
        }

        $json = json_encode($message);
        foreach ($this->connections[$key] as $conn) {
            $conn->send($json);
        }
    }

    /**
     * Register a WebSocket connection for a key
     *
     * @param string $key
     * @param ConnectionInterface $conn
     * @return void
     */
    public function registerConnection(string $key, ConnectionInterface $conn): void
    {
        if (!isset($this->connections[$key])) {
            $this->connections[$key] = [];
        }
        $this->connections[$key][spl_object_id($conn)] = $conn;
    }

    /**
     * Unregister a WebSocket connection
     *
     * @param ConnectionInterface $conn
     * @return void
     */
    public function unregisterConnection(ConnectionInterface $conn): void
    {
        $connId = spl_object_id($conn);
        foreach ($this->connections as $key => $conns) {
            if (isset($conns[$connId])) {
                unset($this->connections[$key][$connId]);
                if (empty($this->connections[$key])) {
                    unset($this->connections[$key]);
                }
            }
        }
    }

    /**
     * Get the log buffer
     *
     * @return LogBuffer
     */
    public function getBuffer(): LogBuffer
    {
        return $this->buffer;
    }

    /**
     * Send HTTP response
     *
     * @param ConnectionInterface $conn
     * @param int $status
     * @param string $statusText
     * @param array|null $data
     * @return void
     */
    private function sendResponse(ConnectionInterface $conn, int $status, string $statusText, ?array $data = null): void
    {
        $body = $data ? json_encode($data) : '';
        $response = "HTTP/1.1 {$status} {$statusText}\r\n" .
                   "Content-Type: application/json\r\n" .
                   "Content-Length: " . strlen($body) . "\r\n" .
                   "Connection: close\r\n" .
                   "\r\n" .
                   $body;
        
        $conn->send($response);
        $conn->close();
    }

    /**
     * Send HTML response
     *
     * @param ConnectionInterface $conn
     * @param int $status
     * @param string $html
     * @return void
     */
    private function sendHtmlResponse(ConnectionInterface $conn, int $status, string $html): void
    {
        $response = "HTTP/1.1 {$status} OK\r\n" .
                   "Content-Type: text/html; charset=utf-8\r\n" .
                   "Content-Length: " . strlen($html) . "\r\n" .
                   "Connection: close\r\n" .
                   "\r\n" .
                   $html;
        
        $conn->send($response);
        $conn->close();
    }

    /**
     * Get viewer HTML
     *
     * @param string $key
     * @return string
     */
    private function getViewerHtml(string $key): string
    {
        // Load viewer template
        $viewerPath = __DIR__ . '/../Viewer/viewer.html';
        if (file_exists($viewerPath)) {
            $html = file_get_contents($viewerPath);
            $html = str_replace('{{KEY}}', htmlspecialchars($key, ENT_QUOTES), $html);
            $html = str_replace('{{WS_URL}}', "ws://{$_SERVER['HTTP_HOST']}/ws", $html);
            return $html;
        }

        // Fallback simple viewer
        return "<!DOCTYPE html><html><head><title>PHPConsoleLog Viewer - {$key}</title></head>" .
               "<body><h1>Viewer for key: {$key}</h1><p>Viewer template not found.</p></body></html>";
    }

    /**
     * Required by HttpServerInterface
     */
    public function onClose(ConnectionInterface $conn)
    {
        // Connection closed
    }

    /**
     * Required by HttpServerInterface
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }

    /**
     * Required by HttpServerInterface
     */
    public function onMessage(ConnectionInterface $from, $msg)
    {
        // Not used for HTTP
    }
}

