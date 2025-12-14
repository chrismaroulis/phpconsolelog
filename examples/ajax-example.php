<?php
/**
 * PHPConsoleLog AJAX Example
 * 
 * This example demonstrates debugging AJAX requests without breaking JSON responses
 * 
 * Start the server first: php examples/server-start.php
 * Open viewer: http://localhost:8080/viewer/ajax-debug
 * Then access this file through a web server or run: php -S localhost:8000 examples/ajax-example.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PHPConsoleLog\Client\Logger;

// Initialize the logger for AJAX debugging
$logger = new Logger('http://localhost:8080/logger', 'ajax-debug');

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log the incoming request
    $logger->info("AJAX Request received");
    $logger->debug("POST data:", $_POST);
    
    // Simulate some processing
    $action = $_POST['action'] ?? 'unknown';
    
    $logger->log("Processing action:", $action);
    
    switch ($action) {
        case 'get_user':
            $userId = $_POST['user_id'] ?? null;
            $logger->debug("Fetching user:", $userId);
            
            // Simulate database query
            $user = [
                'id' => $userId,
                'name' => 'John Doe',
                'email' => 'john@example.com'
            ];
            
            $logger->log("User found:", $user);
            
            // Return JSON response (logging doesn't interfere!)
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'user' => $user]);
            break;
            
        case 'save_data':
            $data = $_POST['data'] ?? null;
            $logger->info("Saving data:", $data);
            
            // Simulate validation
            if (empty($data)) {
                $logger->warning("Validation failed: data is empty");
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Data is required']);
                exit;
            }
            
            $logger->log("Data saved successfully");
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Data saved']);
            break;
            
        default:
            $logger->error("Unknown action:", $action);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
    }
    
    exit;
}

// Serve the HTML page for testing
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHPConsoleLog AJAX Example</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        button {
            padding: 10px 20px;
            margin: 5px;
            cursor: pointer;
            background: #2196f3;
            color: white;
            border: none;
            border-radius: 3px;
        }
        button:hover {
            background: #1976d2;
        }
        #result {
            margin-top: 20px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 5px;
            min-height: 100px;
        }
    </style>
</head>
<body>
    <h1>PHPConsoleLog AJAX Example</h1>
    
    <div class="info">
        <strong>Instructions:</strong><br>
        1. Make sure the server is running: <code>php examples/server-start.php</code><br>
        2. Open the viewer: <a href="http://localhost:8080/viewer/ajax-debug" target="_blank">http://localhost:8080/viewer/ajax-debug</a><br>
        3. Click the buttons below to send AJAX requests<br>
        4. Watch the logs appear in real-time in the viewer window!
    </div>
    
    <h2>Test Actions</h2>
    <button onclick="getUser()">Get User</button>
    <button onclick="saveData()">Save Data</button>
    <button onclick="invalidAction()">Invalid Action</button>
    
    <h2>Response</h2>
    <div id="result">Click a button to send an AJAX request...</div>
    
    <script>
        function getUser() {
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_user&user_id=123'
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('result').innerHTML = '<strong>Response:</strong><br><pre>' + JSON.stringify(data, null, 2) + '</pre>';
            })
            .catch(error => {
                document.getElementById('result').innerHTML = '<strong>Error:</strong> ' + error;
            });
        }
        
        function saveData() {
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=save_data&data=' + encodeURIComponent(JSON.stringify({foo: 'bar', test: 123}))
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('result').innerHTML = '<strong>Response:</strong><br><pre>' + JSON.stringify(data, null, 2) + '</pre>';
            })
            .catch(error => {
                document.getElementById('result').innerHTML = '<strong>Error:</strong> ' + error;
            });
        }
        
        function invalidAction() {
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=invalid_action'
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('result').innerHTML = '<strong>Response:</strong><br><pre>' + JSON.stringify(data, null, 2) + '</pre>';
            })
            .catch(error => {
                document.getElementById('result').innerHTML = '<strong>Error:</strong> ' + error;
            });
        }
    </script>
</body>
</html>

