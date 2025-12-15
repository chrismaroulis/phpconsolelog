# PHPConsoleLog Boilerplate Files - Summary

This document describes the new boilerplate deployment files added to make PHPConsoleLog easier to deploy and use.

## Created Files

### 1. `server.php` (Main Boilerplate Server)

**Purpose:** A simplified, production-ready server file that users can copy directly to their projects.

**Key Features:**
- ✅ Simple, clean code (~95 lines vs 117 in examples)
- ✅ Clear configuration section at the top
- ✅ Sensible defaults (port 8080, bind all interfaces)
- ✅ Customizable buffer size
- ✅ Helpful startup banner with usage instructions
- ✅ Better error messages with solutions
- ✅ Copy-paste ready for any project

**Usage:**
```bash
php server.php
```

**Configuration:**
Edit the configuration section in the file:
```php
$port = 8080;                    // Server port
$host = '0.0.0.0';              // Bind to all interfaces
$bufferSize = 100;              // Number of messages to keep in history
```

---

### 2. `start-server.bat` (Windows Batch Launcher)

**Purpose:** Windows Command Prompt launcher with validation checks.

**Key Features:**
- ✅ Checks if PHP is installed
- ✅ Verifies vendor directory exists
- ✅ Validates server.php location
- ✅ Shows helpful error messages
- ✅ Auto-pause on errors to see messages
- ✅ Double-click to run
- ✅ Command-line parameter support

**Usage:**
```cmd
start-server.bat                    # Default: port 8080, all interfaces
start-server.bat 9000               # Custom port
start-server.bat 9000 127.0.0.1     # Custom port and host
```

**What it checks:**
1. PHP availability in PATH
2. Vendor directory exists (composer install)
3. server.php file exists
4. Provides solutions if checks fail

**Parameters:**
- `%1` - Port number (optional, default: 8080)
- `%2` - Host address (optional, default: 0.0.0.0)

---

### 3. `start-server.ps1` (PowerShell Launcher)

**Purpose:** Modern PowerShell launcher with advanced features.

**Key Features:**
- ✅ Cross-platform (PowerShell Core compatible)
- ✅ Named parameter support (Port, Host)
- ✅ Colored output for better visibility
- ✅ PHP version display
- ✅ Comprehensive error handling
- ✅ Detailed error messages with solutions

**Usage:**
```powershell
# Default (port 8080, all interfaces)
.\start-server.ps1

# Custom port
.\start-server.ps1 -Port 9000

# Custom port and host
.\start-server.ps1 -Port 9000 -Host "127.0.0.1"
```

**Advanced Features:**
- Shows PHP version before starting
- Color-coded messages (Cyan, Green, Yellow, Red)
- Graceful error handling with readable output
- Path validation for all dependencies
- Named parameters with defaults

**Parameters:**
- `-Port` - Port number (default: 8080)
- `-Host` - Host address (default: "0.0.0.0")

---

### 4. `start-server.sh` (Bash Launcher)

**Purpose:** Unix/Linux/Mac launcher with environment checks.

**Key Features:**
- ✅ POSIX-compliant bash script
- ✅ PHP availability check
- ✅ PHP version display
- ✅ Directory and file validation
- ✅ Exit on error (`set -e`)
- ✅ Platform-specific installation instructions
- ✅ Positional parameter support

**Usage:**
```bash
# Make executable (first time only)
chmod +x start-server.sh

# Run with defaults
./start-server.sh

# Custom port
./start-server.sh 9000

# Custom port and host
./start-server.sh 9000 127.0.0.1
```

**Platform Support:**
- Linux (Ubuntu, Debian, CentOS, RHEL)
- macOS (with Homebrew)
- Any Unix-like system with bash

**Parameters:**
- `$1` - Port number (default: 8080)
- `$2` - Host address (default: 0.0.0.0)

---

### 5. `DEPLOYMENT.md` (Deployment Guide)

**Purpose:** Comprehensive guide for deploying PHPConsoleLog in production-like environments.

**Contents:**
- 📦 **Quick Start**: 3-step deployment process
- 🎨 **Customization**: Port, host, buffer size
- 🔑 **Multiple Streams**: Using different keys
- 🚀 **Background Services**: systemd, NSSM, Docker
- 🌐 **Network Config**: Remote access, port forwarding
- 🔒 **Security**: Best practices and warnings
- 🐛 **Troubleshooting**: Common issues and solutions

**Key Sections:**
1. Quick Start (3 steps)
2. Using in Your Application
3. Customization
4. Running as Background Service
5. Network Configuration
6. Security Considerations
7. Troubleshooting

---

## Documentation Updates

### Updated `README.md`

**Changes:**
1. Added "Quick Deployment" section under Installation
2. Updated "Quick Start" with Option A (boilerplate) and Option B (examples)
3. Enhanced "Server Configuration" with boilerplate usage
4. Added copy commands for easy deployment

### Updated `QUICKSTART.md`

**Changes:**
1. Added Option A (boilerplate) to Step 2
2. Kept Option B (examples) for compatibility
3. Clearer instructions for new users

### Updated `composer.json`

**Changes:**
Added new script command:
```json
"serve": "php server.php"
```

Users can now run:
```bash
composer serve
```

---

## User Benefits

### Before (Using examples/server-start.php)

❌ Long path to remember: `php vendor/phpconsolelog/phpconsolelog/examples/server-start.php`  
❌ Can't customize without editing vendor files  
❌ Have to pass port/host as command-line arguments  
❌ Complex code with many features (overwhelming for beginners)  
❌ Manual commands each time  

### After (Using Boilerplate Files)

✅ Simple command: `php server.php` or `start-server.bat`  
✅ Easy customization: Edit config section in your copy  
✅ Clear configuration: Edit settings at the top of the file  
✅ Clean, focused code: Just what you need  
✅ One-click launch: Double-click batch/shell scripts  

---

## Deployment Workflow

### For Package Users

1. **Install Package**
   ```bash
   composer require phpconsolelog/phpconsolelog
   ```

2. **Copy Boilerplate Files**
   ```bash
   cp vendor/phpconsolelog/phpconsolelog/server.php .
   cp vendor/phpconsolelog/phpconsolelog/start-server.bat .  # Windows
   cp vendor/phpconsolelog/phpconsolelog/start-server.sh .   # Linux/Mac
   chmod +x start-server.sh  # Linux/Mac only
   ```

3. **Customize (Optional)**
   Edit `server.php` configuration section

4. **Launch**
   ```bash
   # Windows
   start-server.bat
   
   # Linux/Mac
   ./start-server.sh
   
   # Any platform
   php server.php
   composer serve
   ```

5. **Use in Code**
   ```php
   $logger = new Logger('http://localhost:8080/logger', 'my-app');
   $logger->log('Hello, World!');
   ```

6. **View Logs**
   Open: http://localhost:8080/viewer/my-app

---

## File Comparison

### server.php vs examples/server-start.php

| Feature | server.php | examples/server-start.php |
|---------|-----------|--------------------------|
| Lines of code | ~95 | 117 |
| Configuration | Top of file | Command-line args |
| Target audience | End users | Developers/testers |
| Customization | Easy (edit config) | Hard (CLI args) |
| Error messages | Concise + solutions | Detailed stack traces |
| Startup banner | Minimal + helpful | Elaborate ASCII art |
| Comments | Focused | Extensive |
| Purpose | Production deployment | Development/examples |

Both files are fully functional. Choose based on your needs:
- **Production/User projects**: Use `server.php`
- **Development/Testing**: Use `examples/server-start.php`

---

## Script Comparison

| Feature | .bat | .ps1 | .sh |
|---------|------|------|-----|
| Platform | Windows CMD | Windows PS/Core | Unix/Linux/Mac |
| PHP check | ✅ | ✅ | ✅ |
| Version display | ❌ | ✅ | ✅ |
| Colored output | ❌ | ✅ | Limited |
| Parameters | ❌ | ✅ (Port) | ❌ |
| Auto-pause | ✅ | ✅ | ❌ |
| Exit codes | ✅ | ✅ | ✅ |
| Double-click | ✅ | ✅ | ❌ |

Choose the script that matches your platform and preferences.

---

## Testing Checklist

Before deploying, test the following:

- [ ] PHP syntax check: `php -l server.php`
- [ ] Run server: `php server.php`
- [ ] Open viewer: http://localhost:8080/viewer/test
- [ ] Send log message from client
- [ ] Verify message appears in viewer
- [ ] Test batch script (Windows)
- [ ] Test PowerShell script (Windows)
- [ ] Test shell script (Linux/Mac)
- [ ] Test custom port configuration
- [ ] Test custom host configuration
- [ ] Test buffer size configuration

---

## Future Enhancements

Potential improvements for future versions:

- [ ] Environment variable configuration
- [ ] .env file support
- [ ] SSL/TLS support
- [ ] Authentication/authorization
- [ ] Log rotation
- [ ] Persistent storage backend
- [ ] Configuration file (JSON/YAML)
- [ ] CLI tool with interactive setup
- [ ] Windows Service installer
- [ ] systemd unit file generator
- [ ] Docker image
- [ ] Kubernetes helm chart

---

## Support & Documentation

- 📖 **Full docs**: [README.md](README.md)
- 🚀 **Quick tutorial**: [QUICKSTART.md](QUICKSTART.md)
- 🔧 **Deployment guide**: [DEPLOYMENT.md](DEPLOYMENT.md)
- 💡 **Examples**: [examples/](examples/)
- 🐛 **Issues**: GitHub Issues

---

**Created:** December 15, 2024  
**Purpose:** Simplify PHPConsoleLog deployment for end users  
**Impact:** Reduced deployment complexity from ~10 steps to 3 steps  

✨ **Happy Debugging!** 🐛

