<?php

namespace PHPConsoleLog\Server;

/**
 * In-memory log buffer
 * 
 * Stores recent log messages per key
 */
class LogBuffer
{
    private array $buffers = [];
    private int $maxSize;

    /**
     * Create a new LogBuffer instance
     *
     * @param int $maxSize Maximum number of messages to keep per key
     */
    public function __construct(int $maxSize = 100)
    {
        $this->maxSize = $maxSize;
    }

    /**
     * Add a log message to the buffer
     *
     * @param string $key Session key
     * @param array $message Log message
     * @return void
     */
    public function add(string $key, array $message): void
    {
        if (!isset($this->buffers[$key])) {
            $this->buffers[$key] = [];
        }

        $this->buffers[$key][] = $message;

        // Keep only the most recent messages
        if (count($this->buffers[$key]) > $this->maxSize) {
            array_shift($this->buffers[$key]);
        }
    }

    /**
     * Get all messages for a key
     *
     * @param string $key Session key
     * @return array Array of log messages
     */
    public function get(string $key): array
    {
        return $this->buffers[$key] ?? [];
    }

    /**
     * Clear all messages for a key
     *
     * @param string $key Session key
     * @return void
     */
    public function clear(string $key): void
    {
        if (isset($this->buffers[$key])) {
            $this->buffers[$key] = [];
        }
    }

    /**
     * Remove a key from the buffer
     *
     * @param string $key Session key
     * @return void
     */
    public function remove(string $key): void
    {
        unset($this->buffers[$key]);
    }

    /**
     * Get all active keys
     *
     * @return array Array of keys
     */
    public function getKeys(): array
    {
        return array_keys($this->buffers);
    }

    /**
     * Check if a key exists
     *
     * @param string $key Session key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->buffers[$key]);
    }

    /**
     * Get the number of messages for a key
     *
     * @param string $key Session key
     * @return int
     */
    public function count(string $key): int
    {
        return count($this->buffers[$key] ?? []);
    }
}

