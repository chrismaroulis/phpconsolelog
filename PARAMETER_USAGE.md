# PHPConsoleLog - Server Parameter Usage Guide

This guide explains how to use command-line parameters to customize the server port and host without editing the `server.php` file.

## Overview

All launcher scripts now support optional command-line parameters to override the default port (8080) and host (0.0.0.0) settings.

## Parameter Order

Both parameters are optional:
1. **Port** - The port number to bind the server to (default: 8080)
2. **Host** - The IP address to bind to (default: 0.0.0.0 for all interfaces)

## Usage by Platform

### Windows Batch (start-server.bat)

**Syntax:**
```cmd
start-server.bat [PORT] [HOST]
```

**Examples:**
```cmd
REM Default settings (8080, all interfaces)
start-server.bat

REM Custom port
start-server.bat 9000

REM Custom port and host (localhost only)
start-server.bat 9000 127.0.0.1

REM Custom port on specific network interface
start-server.bat 8080 192.168.1.100
```

---

### PowerShell (start-server.ps1)

**Syntax:**
```powershell
.\start-server.ps1 [-Port <int>] [-Host <string>]
```

**Examples:**
```powershell
# Default settings (8080, all interfaces)
.\start-server.ps1

# Custom port using named parameter
.\start-server.ps1 -Port 9000

# Custom port and host using named parameters
.\start-server.ps1 -Port 9000 -Host "127.0.0.1"

# Alternative: positional parameters also work
.\start-server.ps1 9000 "127.0.0.1"

# Only change host (keep default port)
.\start-server.ps1 -Host "127.0.0.1"
```

**PowerShell Advantages:**
- ✅ Named parameters for clarity
- ✅ Parameter validation (Port must be integer)
- ✅ Built-in help: `Get-Help .\start-server.ps1`
- ✅ Can specify parameters in any order
- ✅ Shows parameter values before starting

---

### Linux/Mac Bash (start-server.sh)

**Syntax:**
```bash
./start-server.sh [PORT] [HOST]
```

**Examples:**
```bash
# Default settings (8080, all interfaces)
./start-server.sh

# Custom port
./start-server.sh 9000

# Custom port and host (localhost only)
./start-server.sh 9000 127.0.0.1

# Custom port on specific network interface
./start-server.sh 8080 192.168.1.100
```

**Note:** The script displays the port and host values before starting for verification.

---

### Direct PHP (server.php)

**Syntax:**
```bash
php server.php [PORT] [HOST]
```

**Examples:**
```bash
# Default settings (8080, all interfaces)
php server.php

# Custom port
php server.php 9000

# Custom port and host (localhost only)
php server.php 9000 127.0.0.1

# Custom port on specific network interface
php server.php 8080 192.168.1.100
```

**Cross-platform:** Works identically on Windows, Linux, and Mac.

---

## Common Use Cases

### 1. Port Already in Use

If port 8080 is already taken:

```bash
# Windows
start-server.bat 8081

# PowerShell
.\start-server.ps1 -Port 8081

# Linux/Mac
./start-server.sh 8081

# Direct PHP
php server.php 8081
```

Then update your client code:
```php
$logger = new Logger('http://localhost:8081/logger', 'my-key');
```

### 2. Localhost Only (Security)

To restrict access to the local machine only:

```bash
# Windows
start-server.bat 8080 127.0.0.1

# PowerShell
.\start-server.ps1 -Port 8080 -Host "127.0.0.1"

# Linux/Mac
./start-server.sh 8080 127.0.0.1

# Direct PHP
php server.php 8080 127.0.0.1
```

### 3. Specific Network Interface

To bind to a specific network interface:

```bash
# Windows
start-server.bat 8080 192.168.1.100

# PowerShell
.\start-server.ps1 -Host "192.168.1.100"

# Linux/Mac
./start-server.sh 8080 192.168.1.100

# Direct PHP
php server.php 8080 192.168.1.100
```

### 4. Development vs Production

Different configurations for different environments:

**Development (accessible from network):**
```bash
php server.php 8080 0.0.0.0
```

**Local Testing (localhost only):**
```bash
php server.php 8080 127.0.0.1
```

**Staging (different port):**
```bash
php server.php 9000 0.0.0.0
```

### 5. Multiple Server Instances

Run multiple servers on different ports:

**Terminal 1:**
```bash
php server.php 8080
```

**Terminal 2:**
```bash
php server.php 8081
```

**Terminal 3:**
```bash
php server.php 8082
```

Then use different clients:
```php
$logger1 = new Logger('http://localhost:8080/logger', 'app1');
$logger2 = new Logger('http://localhost:8081/logger', 'app2');
$logger3 = new Logger('http://localhost:8082/logger', 'app3');
```

---

## Host Values Explained

| Value | Meaning | Use Case |
|-------|---------|----------|
| `0.0.0.0` | All interfaces | Allow connections from anywhere (default) |
| `127.0.0.1` | Localhost only | Security - only local machine can connect |
| `192.168.1.100` | Specific IP | Bind to a specific network interface |
| `localhost` | ⚠️ Not recommended | May resolve to IPv6, use `127.0.0.1` instead |

---

## Permanent Configuration Changes

If you always want to use different defaults, edit `server.php`:

```php
// Change default port from 8080 to 9000
$port = $argv[1] ?? 9000;       // Was: 8080

// Change default host from 0.0.0.0 to localhost only
$host = $argv[2] ?? '127.0.0.1'; // Was: '0.0.0.0'

// Change buffer size
$bufferSize = 200;              // Was: 100
```

Command-line parameters will still override these new defaults.

---

## Troubleshooting

### Parameter Not Working

**Problem:** Changes to port/host seem to have no effect

**Solutions:**
1. Make sure you're passing parameters in the correct order (Port first, then Host)
2. Check for typos in the host address
3. Verify the script is reading from the correct directory
4. Try running with `php server.php [PORT] [HOST]` directly

### Port Still Showing 8080

**Problem:** Server starts on 8080 even when specifying different port

**Solutions:**
1. Verify parameter syntax is correct
2. Check if you're editing the right `server.php` file
3. Ensure there are no spaces in parameter values
4. Try stopping all PHP processes and starting fresh

### Cannot Bind to Host

**Problem:** Error about binding to specified host address

**Solutions:**
1. Verify the IP address exists on your machine: `ipconfig` (Windows) or `ip addr` (Linux)
2. Check if another process is using that IP:port combination
3. Try `0.0.0.0` to bind to all interfaces
4. Ensure you have permissions to bind to the specified address

### Script Not Found Error

**Problem:** The launcher script cannot find `server.php`

**Solutions:**
1. Ensure `server.php` is in the same directory as the launcher script
2. Run the script from the correct directory
3. Verify the file exists: `ls server.php` or `dir server.php`

---

## Testing Your Configuration

After starting the server with custom parameters:

1. **Check the startup banner** - It should show your custom port
2. **Open the viewer**: `http://localhost:[YOUR_PORT]/viewer/test`
3. **Send a test log**:
   ```php
   $logger = new Logger('http://localhost:[YOUR_PORT]/logger', 'test');
   $logger->log('Testing custom port!');
   ```
4. **Verify the message appears** in the viewer

---

## Examples in Different Scenarios

### Docker/Container Environments
```bash
# Bind to all interfaces so container can be accessed
php server.php 8080 0.0.0.0
```

### SSH Port Forwarding
```bash
# Only local access needed for port forwarding
php server.php 8080 127.0.0.1
```

### Shared Development Server
```bash
# Allow team members to connect
php server.php 8080 0.0.0.0
```

### CI/CD Pipeline
```bash
# Use dynamic port from environment
php server.php ${CI_PORT:-8080} 127.0.0.1
```

### Behind Reverse Proxy
```bash
# Localhost only, proxy handles external access
php server.php 8080 127.0.0.1
```

---

## Quick Reference

| What You Want | Command |
|---------------|---------|
| **Default** | `start-server.bat` |
| **Port 9000** | `start-server.bat 9000` |
| **Localhost only** | `start-server.bat 8080 127.0.0.1` |
| **Port 9000, localhost** | `start-server.bat 9000 127.0.0.1` |
| **Specific IP** | `start-server.bat 8080 192.168.1.100` |

(Replace `start-server.bat` with your platform's script)

---

## Learn More

- 📖 **Full Documentation**: [README.md](README.md)
- 🚀 **Quick Tutorial**: [QUICKSTART.md](QUICKSTART.md)
- 🔧 **Deployment Guide**: [DEPLOYMENT.md](DEPLOYMENT.md)
- 📋 **Quick Reference**: [QUICK_REFERENCE.md](QUICK_REFERENCE.md)
- 📝 **Boilerplate Details**: [BOILERPLATE_SUMMARY.md](BOILERPLATE_SUMMARY.md)

---

**Happy Debugging!** 🐛✨

