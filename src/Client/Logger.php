<?php

namespace PHPConsoleLog\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * PHPConsoleLog Client Logger
 * 
 * Sends log messages to a middle-tier WebSocket server for real-time viewing
 */
class Logger
{
    private string $serverUrl;
    private string $key;
    private bool $enabled = true;
    private Client $httpClient;
    private array $options;
    private int $errors_count = 0;

    /**
     * Create a new Logger instance
     *
     * @param string $serverUrl The URL of the logging server
     * @param string $key Unique key for this logging session
     * @param array $options Optional configuration
     */
    public function __construct(string $serverUrl, string $key, array $options = [])
    {
        $this->serverUrl = rtrim($serverUrl, '/');
        $this->key = $key;
        $this->options = array_merge([
            'timeout' => 1.0,
            'async' => true,
            'disable_on_errors' => 5, // number of errors after which logger should be disabled
        ], $options);

        $this->httpClient = new Client([
            'timeout' => $this->options['timeout'],
            'http_errors' => false,
        ]);
    }

    /**
     * Log a debug message
     *
     * @param mixed ...$data Data to log
     * @return void
     */
    public function debug(...$data): void
    {
        $this->send('debug', $data);
    }

    /**
     * Log an info message
     *
     * @param mixed ...$data Data to log
     * @return void
     */
    public function info(...$data): void
    {
        $this->send('info', $data);
    }

    /**
     * Log a message (alias for info)
     *
     * @param mixed ...$data Data to log
     * @return void
     */
    public function log(...$data): void
    {
        $this->info(...$data);
    }

    /**
     * Log a warning message
     *
     * @param mixed ...$data Data to log
     * @return void
     */
    public function warning(...$data): void
    {
        $this->send('warning', $data);
    }

    /**
     * Log an error message
     *
     * @param mixed ...$data Data to log
     * @return void
     */
    public function error(...$data): void
    {
        $this->send('error', $data);
    }

    /**
     * Enable logging
     *
     * @return void
     */
    public function enable(): void
    {
        $this->enabled = true;
    }

    /**
     * Disable logging
     *
     * @return void
     */
    public function disable(): void
    {
        $this->enabled = false;
    }

    /**
     * Check if logging is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Send log data to the server
     *
     * @param string $level Log level
     * @param array $data Data to log
     * @return void
     */
    private function send(string $level, array $data): void
    {
        if (!$this->enabled) {
            return;
        }

        $payload = [
            'key' => $this->key,
            'level' => $level,
            'data' => $this->prepareData($data),
            'timestamp' => time(),
        ];

        // Send asynchronously to avoid blocking the application
        try {
            if ($this->options['debug'] ?? false) {
                echo("PHPConsoleLog: Sending log data to $this->serverUrl - " . json_encode($payload) . "\r\n");
            }
            $this->httpClient->post($this->serverUrl, [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);
        } catch (GuzzleException $e) {
            // Silently fail - logging should never break the application
            // Optionally log to error_log for debugging
            if ($this->options['debug'] ?? false) {
                echo("PHPConsoleLog: Failed to send log - " . $e->getMessage() . "\r\n");
                error_log("PHPConsoleLog: Failed to send log - " . $e->getMessage());
            }
            $this->errors_count++;
            if($this->options['disable_on_errors'] > 0) {
                if ($this->errors_count >= $this->options['disable_on_errors']) {
                    $this->disable();
                    echo("PHPConsoleLog: Force disabled due to errors count - " . $this->errors_count . "\r\n");
                    error_log("PHPConsoleLog: Force disabled due to errors count - " . $this->errors_count);
                }
            }
        }
    }

    /**
     * Clear the error count
     *
     * @return void
     */
    public function clearErrorsCount(): void {
        $this->errors_count = 0;
    }

    /**
     * Get the error count
     *
     * @return int
     */
    public function getErrorsCount(): int {
        return $this->errors_count;
    }

    /**
     * Set the number of errors after which logger should be disabled
     *
     * @param int $count Number of errors
     * @return void
     */
    public function setDisableOnErrorsCount(int $count): void {
        $this->options['disable_on_errors'] = $count;
    }

    /**
     * Prepare data for transmission
     * Converts objects and resources to serializable formats
     *
     * @param array $data Raw data
     * @return array Prepared data
     */
    private function prepareData(array $data): array
    {
        $prepared = [];

        foreach ($data as $item) {
            $prepared[] = $this->prepareItem($item);
        }

        return $prepared;
    }

    /**
     * Prepare a single item for transmission
     *
     * @param mixed $item Item to prepare
     * @return mixed Prepared item
     */
    private function prepareItem($item)
    {
        if (is_object($item)) {
            if ($item instanceof \Throwable) {
                return [
                    '_type' => 'exception',
                    'class' => get_class($item),
                    'message' => $item->getMessage(),
                    'code' => $item->getCode(),
                    'file' => $item->getFile(),
                    'line' => $item->getLine(),
                    'trace' => $item->getTraceAsString(),
                ];
            }
            
            if (method_exists($item, '__toString')) {
                return [
                    '_type' => 'object',
                    'class' => get_class($item),
                    'value' => (string)$item,
                ];
            }

            return [
                '_type' => 'object',
                'class' => get_class($item),
                'properties' => get_object_vars($item),
            ];
        }

        if (is_resource($item)) {
            return [
                '_type' => 'resource',
                'type' => get_resource_type($item),
            ];
        }

        if (is_array($item)) {
            return array_map([$this, 'prepareItem'], $item);
        }

        return $item;
    }
}

