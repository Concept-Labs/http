# Changelog

All notable changes to the Concept-Labs HTTP package will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Comprehensive PEST test suite with 31 tests covering all major components
- Complete documentation including README.md and detailed guides
- Test coverage for Bootstrap, HttpApp, Router, Routes, Middleware, and RequestHandlers
- Mockery integration for effective unit testing
- .gitignore file to exclude vendor and build artifacts

### Changed
- Updated composer.json to include test autoload namespace
- Enabled Composer plugin for PEST

### Fixed
- N/A

## [1.0.0] - Initial Release

### Added
- Bootstrap system for application initialization
- HttpApp with middleware stack management
- Flexible routing with dynamic parameters
- PSR-15 middleware pipeline with priority ordering
- Integration with Singularity DI container
- Configuration-driven architecture
- Support for multiple HTTP methods per route
- Route parameter extraction and injection
- Middleware aggregation with priority-based ordering
- Request handler stack processing
- PSR-7 HTTP message integration
- PSR-11 container integration
- Configuration system with:
  - File globbing support
  - Context variables (BASE, VENDOR)
  - Plugin system (Composer, Comment, Env, Context, Include, Import, Reference, ConfigValue, Extends)
  - Environment variable substitution
  - Configuration inheritance and imports

### Core Components
- **Bootstrap**: Application initialization and container setup
- **HttpApp**: Main application class with middleware management
- **AppFactory**: Factory for creating application instances
- **Router**: PSR-15 middleware for routing requests
- **Route**: Route matching and parameter extraction
- **RouteAggregator**: Route collection and iteration
- **MiddlewareAggregator**: Middleware collection with priority ordering
- **MiddlewareWrapper**: Lazy middleware instantiation wrapper
- **MiddlewareStackHandler**: PSR-15 middleware stack processor
- **RouteHandler**: Route-specific request handler with middleware support

### Interfaces
- **AppInterface**: Application contract
- **AppFactoryInterface**: Application factory contract
- **AppConfigInterface**: Application configuration contract
- **RouterInterface**: Router middleware contract
- **RouteInterface**: Route contract
- **RouteAggregatorInterface**: Route aggregator contract
- **MiddlewareAggregatorInterface**: Middleware aggregator contract
- **MiddlewareWrapperInterface**: Middleware wrapper contract
- **MiddlewareStackHandlerInterface**: Middleware stack handler contract
- **RouteHandlerInterface**: Route handler contract
- **HandlerFactoryInterface**: Handler factory contract

### Conventions
- PSR-12 code style
- PSR-4 autoloading
- PHP 8.2+ with named arguments
- Strict type declarations
- Comprehensive PHPDoc documentation
- SOLID principles
- DRY principle
- KISS principle

## Version History

- **1.0.0** - Initial stable release
- **0.x.x** - Development versions (not documented)

---

For detailed upgrade instructions between versions, see the [Upgrade Guide](docs/upgrade-guide.md).

For a complete list of changes, see the [commit history](https://github.com/Concept-Labs/http/commits/).
