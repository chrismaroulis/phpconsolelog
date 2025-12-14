# Contributing to PHPConsoleLog

Thank you for your interest in contributing to PHPConsoleLog! This document provides guidelines and instructions for contributing.

## Code of Conduct

By participating in this project, you agree to maintain a respectful and inclusive environment for everyone.

## How to Contribute

### Reporting Bugs

If you find a bug, please create an issue on GitHub with:

1. **Clear title** - Describe the issue briefly
2. **Description** - Detailed explanation of the problem
3. **Steps to reproduce** - How to trigger the bug
4. **Expected behavior** - What should happen
5. **Actual behavior** - What actually happens
6. **Environment** - PHP version, OS, etc.

### Suggesting Features

Feature requests are welcome! Please create an issue with:

1. **Use case** - Why is this feature needed?
2. **Proposed solution** - How should it work?
3. **Alternatives** - Any alternative approaches considered?

### Pull Requests

1. **Fork** the repository
2. **Create a branch** from `main` for your changes
3. **Make your changes** following the coding standards below
4. **Test thoroughly** - Ensure your changes work
5. **Commit** with clear, descriptive messages
6. **Push** to your fork
7. **Open a Pull Request** with a clear description

## Development Setup

### Prerequisites

- PHP 7.4 or higher
- Composer
- Git

### Installation

```bash
# Clone your fork
git clone https://github.com/YOUR_USERNAME/phpconsolelog.git
cd phpconsolelog

# Install dependencies
composer install

# Start the test server
php examples/server-start.php
```

### Running Examples

```bash
# Terminal 1: Start the server
php examples/server-start.php

# Terminal 2: Run examples
php examples/client-example.php
```

## Coding Standards

### PHP Style Guide

- **PSR-12** - Follow PSR-12 coding standard
- **Type hints** - Use type hints for parameters and return types
- **Documentation** - Add PHPDoc comments for all public methods
- **Naming** - Use descriptive, camelCase for variables, PascalCase for classes

### Example

```php
<?php

namespace PHPConsoleLog\Example;

/**
 * Example class showing coding standards
 */
class ExampleClass
{
    private string $property;

    /**
     * Constructor with type hints and documentation
     *
     * @param string $value Initial value
     */
    public function __construct(string $value)
    {
        $this->property = $value;
    }

    /**
     * Method with clear documentation
     *
     * @param int $number Input number
     * @return string Formatted result
     */
    public function formatNumber(int $number): string
    {
        return sprintf("Number: %d", $number);
    }
}
```

## Project Structure

```
phpconsolelog/
├── src/
│   ├── Client/          # Client library
│   ├── Server/          # Server components
│   └── Viewer/          # Web viewer interface
├── examples/            # Usage examples
├── tests/              # Unit tests (future)
├── composer.json       # Dependencies
└── README.md           # Documentation
```

## Areas for Contribution

### High Priority

- Unit tests with PHPUnit
- Better error handling
- Performance optimizations
- Documentation improvements

### Feature Ideas

- Persistent storage option
- Authentication system
- Log filtering in viewer
- Export functionality
- Laravel/Symfony packages
- Docker support

### Easy First Issues

- Add more examples
- Improve viewer UI
- Add keyboard shortcuts
- Better mobile support
- Additional log formatters

## Testing

Currently, manual testing is required. Future contributions should include unit tests.

### Manual Testing Checklist

- [ ] Client can send logs to server
- [ ] Viewer receives logs in real-time
- [ ] All log levels display correctly
- [ ] Arrays and objects format properly
- [ ] Clear console works
- [ ] Multiple viewers can connect
- [ ] Buffered logs appear for new viewers
- [ ] Graceful handling of disconnections

## Documentation

When adding features:

1. Update README.md if adding user-facing features
2. Add PHPDoc comments to all new methods
3. Include usage examples
4. Update API reference if needed

## Commit Messages

Write clear commit messages:

- **feat:** New feature
- **fix:** Bug fix
- **docs:** Documentation changes
- **style:** Code style changes (formatting)
- **refactor:** Code refactoring
- **test:** Adding tests
- **chore:** Maintenance tasks

Examples:
```
feat: Add log filtering in viewer
fix: WebSocket reconnection issue
docs: Update installation instructions
```

## Review Process

1. All PRs require review before merging
2. CI checks must pass (when implemented)
3. Changes should be tested
4. Documentation should be updated

## Questions?

If you have questions about contributing:

1. Check existing issues and discussions
2. Create a new issue with the "question" label
3. Be patient - maintainers respond as time permits

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

Thank you for contributing to PHPConsoleLog! 🎉

