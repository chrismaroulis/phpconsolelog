# Changelog

All notable changes to PHPConsoleLog will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial release of PHPConsoleLog
- Client Logger class for sending log messages
- WebSocket server for real-time log streaming
- Web viewer interface with syntax highlighting
- Support for multiple log levels (debug, info, warning, error)
- In-memory log buffer (last 100 messages per key)
- Multiple concurrent viewers support
- Clear console functionality
- Pretty-printing for arrays and objects
- Exception formatting
- Non-blocking async log sending
- Comprehensive documentation and examples
- AJAX debugging example
- MIT License

### Known Issues
- No authentication/authorization (v1 limitation)
- Memory-only storage (logs not persisted)
- No log filtering in viewer yet
- Requires manual server management

## [1.0.0] - TBD

Initial release.

---

## Release Guidelines

### Version Numbers

- **Major (X.0.0)** - Breaking changes
- **Minor (0.X.0)** - New features, backward compatible
- **Patch (0.0.X)** - Bug fixes, backward compatible

### Categories

- **Added** - New features
- **Changed** - Changes to existing functionality
- **Deprecated** - Soon-to-be removed features
- **Removed** - Removed features
- **Fixed** - Bug fixes
- **Security** - Security fixes

