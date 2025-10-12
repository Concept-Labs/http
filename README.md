# Concept-Labs HTTP Application Framework

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://opensource.org/licenses/Apache-2.0)
[![PSR-7](https://img.shields.io/badge/PSR-7-blue.svg)](https://www.php-fig.org/psr/psr-7/)
[![PSR-11](https://img.shields.io/badge/PSR-11-blue.svg)](https://www.php-fig.org/psr/psr-11/)
[![PSR-15](https://img.shields.io/badge/PSR-15-blue.svg)](https://www.php-fig.org/psr/psr-15/)

A powerful, flexible HTTP application framework built on PSR standards and integrated with the Singularity DI container. Part of the Concept-Labs ecosystem.

## 🎯 Philosophy

The Concept-Labs HTTP framework embraces the principles of:

- **Dependency Injection First**: Fully integrated with [Singularity DI Container](https://github.com/Concept-Labs/singularity)
- **PSR Standards**: Built on PSR-7 (HTTP Messages), PSR-11 (Container), and PSR-15 (HTTP Handlers)
- **Configuration-Driven**: Powerful configuration system with plugins, inheritance, and environment variable support
- **Middleware-Centric**: Flexible middleware pipeline with priority-based ordering
- **SOLID Principles**: Clean, maintainable, and testable code architecture

### Concept-Labs Ecosystem

This package is part of the Concept-Labs ecosystem and leverages:
- **Singularity**: Advanced PSR-11 DI container with lifecycle management
- **Config**: Powerful configuration system with plugins and directives
- **HTTP Message**: PSR-7 compliant HTTP message implementation
- **Event Dispatcher**: PSR-14 event dispatcher for application events

## ✨ Key Features

- 🚀 **Bootstrap System**: Simple application bootstrapping with minimal configuration
- 🔀 **Flexible Routing**: Pattern-based routing with dynamic parameters
- 🎭 **Middleware Pipeline**: PSR-15 middleware with priority-based ordering
- 🏗️ **Dependency Injection**: Full integration with Singularity DI container
- ⚙️ **Configuration System**: Advanced configuration with includes, imports, and environment variables
- 🧪 **Fully Tested**: Comprehensive test coverage with PEST
- 📦 **PSR Compliant**: Follows PHP-FIG standards

## 📋 Requirements

- **PHP**: >= 8.2
- **Extensions**: Standard PHP extensions
- **Dependencies**:
  - `concept-labs/singularity`: ^1
  - `concept-labs/http-message`: ^1
  - `concept-labs/event-dispatcher`: ^1
  - `psr/http-server-middleware`: ^1

## 📦 Installation

Install via Composer:

```bash
composer require concept-labs/http
```

## 🚀 Quick Start

### Basic Application

```php
<?php
require_once 'vendor/autoload.php';

use Concept\Http\Bootstrap;

// Create bootstrap instance
$bootstrap = new Bootstrap(
    base: __DIR__,
    configSource: 'config/*.json'
);

// Create and run application
$app = $bootstrap->app();
$app->run();
```

### Configuration Example

Create `config/app.json`:

```json
{
    "app": {
        "name": "My Application",
        "debug": true
    },
    
    "middleware": {
        "router": {
            "preference": "Concept\\Http\\Router\\RouterInterface",
            "priority": 100,
            "route": {
                "/": {
                    "method": ["GET"],
                    "handler": {
                        "preference": "App\\Handler\\HomeHandler"
                    }
                },
                "/api/users/{id}": {
                    "method": ["GET", "PUT", "DELETE"],
                    "handler": {
                        "preference": "App\\Handler\\UserHandler"
                    }
                }
            }
        }
    }
}
```

### Custom Middleware

```php
<?php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request, 
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Authentication logic here
        
        return $handler->handle($request);
    }
}
```

## 📚 Documentation

- **[Installation Guide](docs/installation.md)** - Detailed installation and setup
- **[Configuration](docs/configuration.md)** - Complete configuration reference
- **[Routing](docs/routing.md)** - Routing system and patterns
- **[Middleware](docs/middleware.md)** - Middleware pipeline and creation
- **[Dependency Injection](docs/dependency-injection.md)** - DI container integration
- **[Examples](docs/examples.md)** - Real-world usage examples
- **[API Reference](docs/api-reference.md)** - Complete API documentation

## 🏗️ Architecture

### Core Components

```
┌─────────────────────────────────────────────────────┐
│                    Bootstrap                         │
│  • Initializes container and configuration          │
│  • Creates application instance                      │
└──────────────────┬──────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────┐
│                   HttpApp                            │
│  • Manages middleware stack                         │
│  • Processes HTTP requests                          │
│  • Handles response generation                      │
└──────────────────┬──────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────┐
│              Middleware Pipeline                     │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐          │
│  │ Router   │→ │  Auth    │→ │ Handler  │          │
│  └──────────┘  └──────────┘  └──────────┘          │
└─────────────────────────────────────────────────────┘
```

### Request Flow

1. **Bootstrap** initializes the DI container and loads configuration
2. **AppFactory** creates the HttpApp instance with middleware
3. **Middleware Pipeline** processes the request through configured middleware
4. **Router** matches the request to a route
5. **Handler** generates the response
6. **Response** is sent back through the middleware chain

## 🧪 Testing

Run the test suite:

```bash
# Run all tests
./vendor/bin/pest

# Run with coverage
./vendor/bin/pest --coverage

# Run specific test file
./vendor/bin/pest tests/Unit/Router/RouteTest.php
```

### Test Coverage

- ✅ Bootstrap initialization and configuration loading
- ✅ HTTP application middleware management
- ✅ Routing with dynamic parameters
- ✅ Middleware aggregation and priority ordering
- ✅ Request handler stack processing

## 🔧 Conventions

### Code Style

- **PSR-12**: Extended coding style guide
- **PSR-4**: Autoloading standard
- **Named Arguments**: Leverage PHP 8.2+ features
- **Type Declarations**: Strict types enabled

### Configuration

- Use **JSON format** for all configuration files
- Use **package-level** configuration in `concept.json` for DI bindings
- Use **application** configuration in `config/*.json` for app settings
- Leverage configuration **directives** (@import, @include, @extends)
- Use **environment variables** with `${ENV_VAR}` syntax

### Dependency Injection

- Use **constructor injection** for dependencies
- Implement **Injectable** interface for automatic DI
- Define **preferences** in `concept.json` for interface bindings
- Use **shared: true** for singleton services
- Avoid **service locator** anti-pattern

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Setup

```bash
# Clone repository
git clone https://github.com/Concept-Labs/http.git
cd http

# Install dependencies
composer install

# Run tests
./vendor/bin/pest
```

## 📖 Examples

### Full Application Example

```php
<?php
require_once 'vendor/autoload.php';

use Concept\Http\Bootstrap;

// Bootstrap with configuration
$bootstrap = new Bootstrap(
    base: __DIR__,
    configSource: 'config/*.json'
);

// Get application
$app = $bootstrap->app();

// Add runtime middleware (optional)
$app->addMiddleware(
    new App\Middleware\LoggingMiddleware(),
    priority: 200
);

// Run application
$app->run();
```

See [docs/examples.md](docs/examples.md) for more comprehensive examples.

## 🔗 Quick Links

- **GitHub**: [Concept-Labs/http](https://github.com/Concept-Labs/http)
- **Singularity DI**: [Concept-Labs/singularity](https://github.com/Concept-Labs/singularity)
- **Issues**: [Report Bug](https://github.com/Concept-Labs/http/issues)
- **Discussions**: [Community Forum](https://github.com/Concept-Labs/http/discussions)

## ❓ FAQs

**Q: How is this different from other PHP frameworks?**  
A: Concept-Labs HTTP is not a full-stack framework but a lightweight, PSR-compliant HTTP application layer. It focuses on DI-first architecture and configuration-driven development.

**Q: Can I use this without Singularity DI?**  
A: Technically yes, by providing your own PSR-11 container, but Singularity integration provides the best experience with advanced features like lifecycle management and configuration-based DI.

**Q: Is it production-ready?**  
A: Yes! The package is fully tested and follows SOLID principles and PSR standards.

**Q: How do I handle CORS, sessions, etc.?**  
A: Implement custom middleware for these concerns. Check the [Middleware Guide](docs/middleware.md) for examples.

## 📄 License

Copyright (c) 2025 Concept-Labs

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

## 💝 Support

If you find this project useful, please consider:
- ⭐ Starring the repository
- 🐛 Reporting bugs and issues
- 💡 Suggesting new features
- 🤝 Contributing code or documentation

---

Built with ❤️ by [Concept-Labs](https://github.com/Concept-Labs)
