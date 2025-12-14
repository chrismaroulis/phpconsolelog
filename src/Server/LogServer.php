<?php

namespace PHPConsoleLog\Server;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

/**
 * WebSocket server for real-time log streaming
 * 
 * Handles WebSocket connections from viewer browsers
 */
class LogServer implements MessageComponentInterface
{
    private LogHandler $handler;
    private array $clients = [];

    /**
     * Create a new LogServer instance
     *
     * @param LogHandler $handler Log handler instance
     */
    public function __construct(LogHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Handle new WebSocket connection
     *
     * @param ConnectionInterface $conn
     * @return void
     */
    public function onOpen(ConnectionInterface $conn)
    {
        echo "New WebSocket connection: {$conn->resourceId}\n";
        $this->clients[$conn->resourceId] = [
            'conn' => $conn,
            'key' => null,
        ];
    }

    /**
     * Handle incoming WebSocket message
     *
     * @param ConnectionInterface $from
     * @param string $msg
     * @return void
     */
    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);

        if (!$data || !isset($data['action'])) {
            $from->send(json_encode(['error' => 'Invalid message format']));
            return;
        }

        switch ($data['action']) {
            case 'register':
                $this->handleRegister($from, $data);
                break;

            case 'clear':
                $this->handleClear($from, $data);
                break;

            default:
                $from->send(json_encode(['error' => 'Unknown action']));
        }
    }

    /**
     * Handle viewer registration
     *
     * @param ConnectionInterface $conn
     * @param array $data
     * @return void
     */
    private function handleRegister(ConnectionInterface $conn, array $data): void
    {
        if (!isset($data['key'])) {
            $conn->send(json_encode(['error' => 'Key is required']));
            return;
        }

        $key = $data['key'];
        $connId = $conn->resourceId;

        // Store the key for this connection
        $this->clients[$connId]['key'] = $key;

        // Register with the handler
        $this->handler->registerConnection($key, $conn);

        // Send buffered logs
        $bufferedLogs = $this->handler->getBuffer()->get($key);
        
        $conn->send(json_encode([
            'type' => 'registered',
            'key' => $key,
            'bufferedLogs' => $bufferedLogs,
        ]));

        echo "Viewer registered for key: {$key} (connection: {$connId})\n";
    }

    /**
     * Handle clear console request
     *
     * @param ConnectionInterface $conn
     * @param array $data
     * @return void
     */
    private function handleClear(ConnectionInterface $conn, array $data): void
    {
        if (!isset($data['key'])) {
            $conn->send(json_encode(['error' => 'Key is required']));
            return;
        }

        $key = $data['key'];

        // Clear the buffer
        $this->handler->getBuffer()->clear($key);

        // Notify all viewers for this key
        $this->handler->broadcastToKey($key, [
            'type' => 'cleared',
        ]);

        echo "Console cleared for key: {$key}\n";
    }

    /**
     * Handle connection close
     *
     * @param ConnectionInterface $conn
     * @return void
     */
    public function onClose(ConnectionInterface $conn)
    {
        $connId = $conn->resourceId;
        
        if (isset($this->clients[$connId])) {
            $key = $this->clients[$connId]['key'];
            echo "Connection closed: {$connId}" . ($key ? " (key: {$key})" : "") . "\n";
            
            // Unregister from handler
            $this->handler->unregisterConnection($conn);
            
            unset($this->clients[$connId]);
        }
    }

    /**
     * Handle connection error
     *
     * @param ConnectionInterface $conn
     * @param \Exception $e
     * @return void
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Error on connection {$conn->resourceId}: {$e->getMessage()}\n";
        $conn->close();
    }
}

