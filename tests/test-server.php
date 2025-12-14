<?php
/**
 * PHPConsoleLog Server Test Suite
 * 
 * This script tests the server functionality before deployment
 * 
 * Usage: php tests/test-server.php
 * 
 * Note: This script will start the server in the background, run tests, and stop it.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PHPConsoleLog\Client\Logger;

class ServerTest
{
    private $testsPassed = 0;
    private $testsFailed = 0;
    private $serverProcess = null;
    private $testPort = 8888;
    private $testHost = 'localhost';
    private $serverUrl;
    private $testKey = 'test-key-' . time();

    public function __construct()
    {
        $this->serverUrl = "http://{$this->testHost}:{$this->testPort}";
    }

    public function run()
    {
        $this->printHeader();
        
        echo "🔧 Starting test server on port {$this->testPort}...\n\n";
        
        if (!$this->startServer()) {
            echo "❌ Failed to start server. Make sure port {$this->testPort} is available.\n";
            return 1;
        }

        sleep(2); // Give server time to start

        echo "✅ Server started successfully\n\n";
        echo "Running tests...\n";
        echo str_repeat("─", 60) . "\n\n";

        // Run all tests
        $this->testServerHealth();
        $this->testBasicLogging();
        $this->testLogLevels();
        $this->testComplexData();
        $this->testMultipleMessages();
        $this->testViewerEndpoint();
        $this->testInvalidEndpoint();

        echo "\n" . str_repeat("─", 60) . "\n";
        $this->printResults();

        $this->stopServer();

        return $this->testsFailed > 0 ? 1 : 0;
    }

    private function printHeader()
    {
        echo "╔════════════════════════════════════════════════════════════╗\n";
        echo "║         PHPConsoleLog Server Test Suite                   ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n\n";
    }

    private function startServer()
    {
        $command = sprintf(
            'php %s %d %s > %s 2>&1 &',
            escapeshellarg(__DIR__ . '/../examples/server-start.php'),
            $this->testPort,
            $this->testHost,
            escapeshellarg(__DIR__ . '/server-output.log')
        );

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows
            $command = sprintf(
                'start /B php %s %d %s > %s 2>&1',
                escapeshellarg(__DIR__ . '/../examples/server-start.php'),
                $this->testPort,
                $this->testHost,
                escapeshellarg(__DIR__ . '/server-output.log')
            );
            pclose(popen($command, 'r'));
        } else {
            // Unix-like
            exec($command);
        }

        // Wait and check if server is running
        $maxAttempts = 10;
        for ($i = 0; $i < $maxAttempts; $i++) {
            sleep(1);
            if ($this->isServerRunning()) {
                return true;
            }
        }

        return false;
    }

    private function isServerRunning()
    {
        $ch = curl_init($this->serverUrl . '/viewer/' . $this->testKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }

    private function stopServer()
    {
        echo "\n🛑 Stopping test server...\n";
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows - kill PHP processes on the test port
            exec("for /f \"tokens=5\" %a in ('netstat -aon ^| find \":{$this->testPort}\" ^| find \"LISTENING\"') do taskkill /F /PID %a 2>nul");
        } else {
            // Unix-like
            exec("lsof -ti tcp:{$this->testPort} | xargs kill -9 2>/dev/null");
        }

        // Clean up log file
        $logFile = __DIR__ . '/server-output.log';
        if (file_exists($logFile)) {
            unlink($logFile);
        }

        echo "✅ Server stopped\n";
    }

    private function testServerHealth()
    {
        $testName = "Server Health Check";
        echo "Test: {$testName}... ";

        $ch = curl_init($this->serverUrl . '/viewer/' . $this->testKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && strpos($result, 'PHPConsoleLog') !== false) {
            $this->pass($testName);
        } else {
            $this->fail($testName, "Expected HTTP 200, got {$httpCode}");
        }
    }

    private function testBasicLogging()
    {
        $testName = "Basic Logging";
        echo "Test: {$testName}... ";

        try {
            $logger = new Logger($this->serverUrl . '/logger', $this->testKey);
            $logger->log("Test message");
            $this->pass($testName);
        } catch (Exception $e) {
            $this->fail($testName, $e->getMessage());
        }
    }

    private function testLogLevels()
    {
        $testName = "All Log Levels";
        echo "Test: {$testName}... ";

        try {
            $logger = new Logger($this->serverUrl . '/logger', $this->testKey);
            $logger->debug("Debug message");
            $logger->info("Info message");
            $logger->log("Log message");
            $logger->warning("Warning message");
            $logger->error("Error message");
            $this->pass($testName);
        } catch (Exception $e) {
            $this->fail($testName, $e->getMessage());
        }
    }

    private function testComplexData()
    {
        $testName = "Complex Data Types";
        echo "Test: {$testName}... ";

        try {
            $logger = new Logger($this->serverUrl . '/logger', $this->testKey);
            
            // Array
            $logger->log(['key' => 'value', 'number' => 42]);
            
            // Object
            $obj = new stdClass();
            $obj->prop = 'test';
            $logger->log($obj);
            
            // Exception
            $exception = new Exception("Test exception");
            $logger->error($exception);
            
            // Mixed types
            $logger->log("String", 123, true, null, [1, 2, 3]);
            
            $this->pass($testName);
        } catch (Exception $e) {
            $this->fail($testName, $e->getMessage());
        }
    }

    private function testMultipleMessages()
    {
        $testName = "Multiple Sequential Messages";
        echo "Test: {$testName}... ";

        try {
            $logger = new Logger($this->serverUrl . '/logger', $this->testKey);
            
            for ($i = 1; $i <= 10; $i++) {
                $logger->log("Message #{$i}");
            }
            
            $this->pass($testName);
        } catch (Exception $e) {
            $this->fail($testName, $e->getMessage());
        }
    }

    private function testViewerEndpoint()
    {
        $testName = "Viewer Endpoint";
        echo "Test: {$testName}... ";

        $ch = curl_init($this->serverUrl . '/viewer/' . $this->testKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && 
            strpos($result, 'PHPConsoleLog') !== false &&
            strpos($result, $this->testKey) !== false) {
            $this->pass($testName);
        } else {
            $this->fail($testName, "Viewer page not properly rendered");
        }
    }

    private function testInvalidEndpoint()
    {
        $testName = "Invalid Endpoint (404)";
        echo "Test: {$testName}... ";

        $ch = curl_init($this->serverUrl . '/invalid-endpoint');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 404) {
            $this->pass($testName);
        } else {
            $this->fail($testName, "Expected HTTP 404, got {$httpCode}");
        }
    }

    private function pass($testName)
    {
        echo "✅ PASS\n";
        $this->testsPassed++;
    }

    private function fail($testName, $reason)
    {
        echo "❌ FAIL\n";
        echo "  Reason: {$reason}\n";
        $this->testsFailed++;
    }

    private function printResults()
    {
        $total = $this->testsPassed + $this->testsFailed;
        $passRate = $total > 0 ? round(($this->testsPassed / $total) * 100, 1) : 0;

        echo "\n";
        echo "╔════════════════════════════════════════════════════════════╗\n";
        echo "║                     TEST RESULTS                           ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n";
        echo "\n";
        echo "  Total Tests:  {$total}\n";
        echo "  ✅ Passed:     {$this->testsPassed}\n";
        echo "  ❌ Failed:     {$this->testsFailed}\n";
        echo "  Pass Rate:    {$passRate}%\n";
        echo "\n";

        if ($this->testsFailed === 0) {
            echo "🎉 All tests passed! Your server is ready for deployment.\n";
        } else {
            echo "⚠️  Some tests failed. Please review the errors above.\n";
        }
        echo "\n";
    }
}

// Run tests
$tester = new ServerTest();
exit($tester->run());
