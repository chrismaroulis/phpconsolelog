# PHPConsoleLog - Project Structure

This document explains the organization and architecture of the PHPConsoleLog project.

## Directory Structure

```
phpconsolelog/
├── src/                        # Source code
│   ├── Client/                 # Client library
│   │   └── Logger.php          # Main logger class for applications
│   ├── Server/                 # Server components
│   │   ├── LogBuffer.php       # In-memory log storage
│   │   ├── LogHandler.php      # HTTP endpoint handler
│   │   └── LogServer.php       # WebSocket server
│   └── Viewer/                 # Web viewer interface
│       └── viewer.html         # Browser-based log viewer
├── examples/                   # Usage examples
│   ├── server-start.php        # Server startup script
│   ├── client-example.php      # Basic usage example
│   └── ajax-example.php        # AJAX debugging example
├── tests/                      # Unit tests (future)
├── composer.json               # Composer configuration
├── phpunit.xml                 # PHPUnit configuration
├── .gitignore                  # Git ignore rules
├── LICENSE                     # MIT License
├── README.md                   # Main documentation
├── QUICKSTART.md               # Quick start guide
├── CONTRIBUTING.md             # Contribution guidelines
├── CHANGELOG.md                # Version history
└── PROJECT_STRUCTURE.md        # This file
```

## Component Overview

### 1. Client Library (`src/Client/`)

**Purpose:** Provides the API that applications use to send log messages.

#### Logger.php

The main class that developers interact with.

**Key Features:**
- HTTP POST to send logs to the server
- Multiple log levels (debug, info, warning, error)
- Non-blocking async requests
- Graceful error handling
- Data serialization for objects/arrays

**Public API:**
```php
$logger = new Logger(string $serverUrl, string $key, array $options = [])
$logger->debug(...$data): void
$logger->info(...$data): void
$logger->log(...$data): void
$logger->warning(...$data): void
$logger->error(...$data): void
$logger->enable(): void
$logger->disable(): void
$logger->isEnabled(): bool
```

**Data Flow:**
1. Application calls `$logger->log("message")`
2. Logger serializes data (objects → arrays, etc.)
3. Sends HTTP POST to server endpoint
4. Returns immediately (non-blocking)

### 2. Server Components (`src/Server/`)

**Purpose:** Receives logs from clients and broadcasts to viewers.

#### LogBuffer.php

In-memory storage for recent log messages.

**Responsibilities:**
- Store last N messages per key (default: 100)
- Provide buffer history to new viewers
- Clear logs on request
- Manage multiple keys simultaneously

**Key Methods:**
```php
add(string $key, array $message): void
get(string $key): array
clear(string $key): void
```

**Data Structure:**
```php
[
    'key1' => [
        ['type' => 'log', 'level' => 'info', ...],
        ['type' => 'log', 'level' => 'error', ...],
    ],
    'key2' => [...]
]
```

#### LogHandler.php

HTTP endpoint handler for log messages and viewer requests.

**Responsibilities:**
- Handle `POST /logger` requests from clients
- Handle `GET /viewer/{key}` requests
- Format log data for display
- Coordinate with LogBuffer and WebSocket server

**HTTP Endpoints:**
- `POST /logger` - Receives log messages
  - Request: `{key, level, data, timestamp}`
  - Response: `{success: true}`
- `GET /viewer/{key}` - Serves viewer HTML page

**Data Flow:**
1. Receives POST request with log data
2. Stores in LogBuffer
3. Broadcasts to all WebSocket connections for that key
4. Returns success response

#### LogServer.php

WebSocket server for real-time communication with viewers.

**Responsibilities:**
- Accept WebSocket connections from browsers
- Register viewers with their keys
- Handle viewer actions (register, clear)
- Send buffered logs to new connections
- Broadcast new logs in real-time

**WebSocket Protocol:**

**Client → Server:**
```json
{
  "action": "register",
  "key": "my-key"
}
```

```json
{
  "action": "clear",
  "key": "my-key"
}
```

**Server → Client:**
```json
{
  "type": "registered",
  "key": "my-key",
  "bufferedLogs": [...]
}
```

```json
{
  "type": "log",
  "level": "info",
  "data": [...],
  "timestamp": 1234567890,
  "formatted": "..."
}
```

```json
{
  "type": "cleared"
}
```

### 3. Web Viewer (`src/Viewer/`)

**Purpose:** Browser-based interface for viewing logs in real-time.

#### viewer.html

Single-page application with embedded CSS and JavaScript.

**Features:**
- Dark theme console interface
- WebSocket connection to server
- Syntax highlighting for JSON
- Color-coded log levels
- Auto-scroll functionality
- Clear console button
- Connection status indicator

**UI Components:**
- Header: Title, status indicator, clear button
- Console: Scrollable log display
- Log entries: Timestamp, level, content

**JavaScript Flow:**
1. Connect to WebSocket server
2. Send register message with key
3. Receive buffered logs
4. Display logs as they arrive
5. Handle clear requests

## Data Flow

### Complete Logging Flow

```
┌─────────────┐
│ PHP App     │
│             │
│ $logger->   │
│   log()     │
└──────┬──────┘
       │
       │ HTTP POST
       │ {key, level, data}
       │
       ▼
┌─────────────┐
│ LogHandler  │◄──────┐
│             │       │
│ POST /logger│       │
└──────┬──────┘       │
       │              │
       │ Store        │ Broadcast
       │              │
       ▼              │
┌─────────────┐       │
│ LogBuffer   │       │
│             │       │
│ In-Memory   │       │
└─────────────┘       │
                      │
       ┌──────────────┘
       │
       │ WebSocket
       │ {type: "log", ...}
       │
       ▼
┌─────────────┐
│ LogServer   │
│             │
│ WebSocket   │
└──────┬──────┘
       │
       │ Send to browsers
       │
       ▼
┌─────────────┐
│ Viewer      │
│             │
│ Browser UI  │
└─────────────┘
```

### Viewer Connection Flow

```
┌─────────────┐
│ Browser     │
│             │
│ Open viewer │
│ URL         │
└──────┬──────┘
       │
       │ HTTP GET /viewer/{key}
       │
       ▼
┌─────────────┐
│ LogHandler  │
│             │
│ Serve HTML  │
└──────┬──────┘
       │
       │ viewer.html
       │
       ▼
┌─────────────┐
│ Browser     │
│             │
│ Execute JS  │
└──────┬──────┘
       │
       │ WebSocket Connect
       │
       ▼
┌─────────────┐
│ LogServer   │
│             │
│ Register    │
└──────┬──────┘
       │
       │ Send buffered logs
       │
       ▼
┌─────────────┐
│ Viewer      │
│             │
│ Display     │
└─────────────┘
```

## Key Design Decisions

### 1. Non-Blocking Logging

**Decision:** Use async HTTP requests with short timeout  
**Rationale:** Logging should never slow down the application  
**Implementation:** Guzzle HTTP client with 1-second timeout

### 2. Memory-Only Storage

**Decision:** Store logs in RAM, not persistent storage  
**Rationale:** Simple, fast, suitable for development debugging  
**Trade-off:** Logs lost on server restart (acceptable for v1)

### 3. WebSockets for Real-Time

**Decision:** Use WebSockets instead of polling  
**Rationale:** Instant updates, lower overhead  
**Library:** Ratchet PHP WebSocket library

### 4. Key-Based Isolation

**Decision:** Use simple string keys for log streams  
**Rationale:** Easy to use, no authentication needed for dev tool  
**Security:** Keys provide basic isolation but not security

### 5. Built-In Viewer

**Decision:** Include HTML viewer in the library  
**Rationale:** No separate tools needed, works out of the box  
**Implementation:** Single HTML file with embedded CSS/JS

## Extension Points

Areas where the library can be extended:

### 1. Storage Backends

Add persistent storage by implementing a new storage class:

```php
interface StorageInterface {
    public function add(string $key, array $message): void;
    public function get(string $key): array;
    public function clear(string $key): void;
}

class DatabaseStorage implements StorageInterface { ... }
```

### 2. Authentication

Add authentication to the LogHandler:

```php
class AuthLogHandler extends LogHandler {
    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null) {
        if (!$this->authenticate($request)) {
            $this->sendResponse($conn, 401, 'Unauthorized');
            return;
        }
        parent::onOpen($conn, $request);
    }
}
```

### 3. Custom Formatters

Add custom data formatters:

```php
interface FormatterInterface {
    public function format($data): string;
}

class JsonFormatter implements FormatterInterface { ... }
```

### 4. Framework Integration

Create framework-specific packages:

```php
// Laravel Service Provider
class PHPConsoleLogServiceProvider extends ServiceProvider { ... }

// Symfony Bundle
class PHPConsoleLogBundle extends Bundle { ... }
```

## Testing Strategy

### Unit Tests (Future)

- **Client/Logger.php** - Test data serialization, HTTP requests
- **Server/LogBuffer.php** - Test buffer management, limits
- **Server/LogHandler.php** - Test message handling, formatting
- **Server/LogServer.php** - Test WebSocket protocol

### Integration Tests (Future)

- End-to-end log flow
- Multiple concurrent clients
- Viewer connections and disconnections
- Buffer overflow handling

### Manual Testing

Current testing approach:
1. Run server
2. Run examples
3. Verify in viewer
4. Test edge cases

## Performance Considerations

### Client Side

- **Non-blocking:** Async requests don't block execution
- **Timeout:** 1-second timeout prevents hanging
- **Graceful failure:** Errors caught and ignored

### Server Side

- **Memory usage:** ~100 messages × ~1KB = ~100KB per key
- **WebSocket overhead:** Minimal, event-driven
- **Concurrency:** Ratchet handles multiple connections efficiently

### Scaling Limits

Current architecture suitable for:
- ✅ Development debugging
- ✅ Small teams (1-10 developers)
- ✅ Moderate log volume (<1000 msg/sec)

Not suitable for:
- ❌ Production logging
- ❌ High-volume applications
- ❌ Long-term log storage

## Dependencies

### Runtime

- **PHP 7.4+** - Core language
- **cboden/ratchet ^0.4** - WebSocket server
- **guzzlehttp/guzzle ^7.0** - HTTP client

### Development

- **phpunit/phpunit ^9.0|^10.0** - Unit testing (future)

## Future Enhancements

See [CONTRIBUTING.md](CONTRIBUTING.md) for contribution ideas.

Priority enhancements:
1. Unit tests
2. Persistent storage option
3. Authentication/authorization
4. Log filtering in viewer
5. Export functionality

---

For questions about the architecture, open an issue on GitHub or check the [README.md](README.md).

