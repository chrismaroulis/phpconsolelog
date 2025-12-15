#!/bin/bash
#
# PHPConsoleLog Server Launcher (Unix/Linux/Mac)
#
# This script starts the PHPConsoleLog server.
#
# Usage:
#   ./start-server.sh              (default port 8080, all interfaces)
#   ./start-server.sh 9000         (custom port, all interfaces)
#   ./start-server.sh 9000 127.0.0.1  (custom port and host)
#
# Make executable: chmod +x start-server.sh

set -e  # Exit on error

# Get optional parameters
PORT=${1:-8080}
HOST=${2:-0.0.0.0}

echo ""
echo "Starting PHPConsoleLog Server..."
echo "Port: $PORT"
echo "Host: $HOST"
echo ""

# Get script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "ERROR: PHP is not found in your PATH."
    echo ""
    echo "Please install PHP:"
    echo "  Ubuntu/Debian: sudo apt-get install php-cli"
    echo "  macOS: brew install php"
    echo "  CentOS/RHEL: sudo yum install php-cli"
    echo ""
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo "PHP Version: $PHP_VERSION"

# Check if vendor directory exists
if [ ! -d "$SCRIPT_DIR/vendor" ]; then
    echo ""
    echo "ERROR: Vendor directory not found."
    echo ""
    echo "Please run: composer install"
    echo ""
    exit 1
fi

# Check if server.php exists
if [ ! -f "$SCRIPT_DIR/server.php" ]; then
    echo ""
    echo "ERROR: server.php not found."
    echo ""
    echo "Make sure you're running this script from the project root."
    echo ""
    exit 1
fi

# Start the server with optional port and host arguments
echo ""
echo "Launching server..."
echo ""

php "$SCRIPT_DIR/server.php" "$PORT" "$HOST"

# If we get here, the server has stopped
EXIT_CODE=$?
if [ $EXIT_CODE -ne 0 ]; then
    echo ""
    echo "Server stopped with an error (exit code: $EXIT_CODE)"
    echo ""
    exit $EXIT_CODE
fi

