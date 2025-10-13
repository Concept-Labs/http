# Concept-Labs HTTP Application Ecosystem

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://opensource.org/licenses/Apache-2.0)
[![PSR-7](https://img.shields.io/badge/PSR-7-blue.svg)](https://www.php-fig.org/psr/psr-7/)
[![PSR-11](https://img.shields.io/badge/PSR-11-blue.svg)](https://www.php-fig.org/psr/psr-11/)
[![PSR-15](https://img.shields.io/badge/PSR-15-blue.svg)](https://www.php-fig.org/psr/psr-15/)

A low-level, PSR-compliant HTTP application foundation built on configuration-driven architecture and integrated with the Singularity DI container. Part of the Concept-Labs ecosystem.

## 🎯 Philosophy

The Concept-Labs HTTP ecosystem embraces the principles of:

- **Configuration-Driven Architecture**: Build entirely different applications (REST APIs, web apps, CLI runners) through configuration alone. See [Configuration Package](https://github.com/Concept-Labs/config) for the powerful configuration system.
- **Dependency Injection First**: Fully integrated with [Singularity DI Container](https://github.com/Concept-Labs/singularity)
- **PSR Standards**: Built on PSR-7 (HTTP Messages), PSR-11 (Container), and PSR-15 (HTTP Handlers)
- **Lazy Initialization**: Middleware wrappers ensure components are only instantiated when actually needed
- **Low-Level Foundation**: Provides core HTTP handling that higher-level packages build upon (like [simple-http](https://github.com/Concept-Labs/simple-http))
- **SOLID Principles**: Clean, maintainable, and testable code architecture

### Concept-Labs Ecosystem

This package is part of the Concept-Labs ecosystem and leverages:
- **Singularity**: Advanced PSR-11 DI container with lifecycle management
- **Config**: Powerful [configuration system](https://github.com/Concept-Labs/config) enabling configuration-driven application building
- **HTTP Message**: PSR-7 compliant HTTP message implementation
- **Event Dispatcher**: PSR-14 event dispatcher for application events

This is a **low-level** HTTP application foundation. For higher-level applications, see:
- **[simple-http](https://github.com/Concept-Labs/simple-http)**: Higher-level HTTP application with additional features

## ✨ Key Features

- 🚀 **Bootstrap System**: Simple application bootstrapping with minimal configuration
- 🎭 **Middleware Pipeline**: PSR-15 middleware with priority-based ordering and lazy initialization
- 🏗️ **Dependency Injection**: Full integration with Singularity DI container
- ⚙️ **Configuration System**: Build different applications through configuration (see [Config Package](https://github.com/Concept-Labs/config))
- 🔌 **Extensible**: Low-level foundation for higher-level packages
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

// Create bootstrap instance with configuration
$bootstrap = new Bootstrap(
    base: __DIR__,
    configSource: 'config/*.json'
);

// Create and run application
$app = $bootstrap->app();
$app->run();
```

### Configuration-Driven Setup

The power of this ecosystem lies in its configuration-driven architecture. Your entire application is defined through configuration:

Create `config/app.json`:

```json
{
    "app": {
        "name": "My Application",
        "debug": true
    },
    
    "middleware": {
        "custom": {
            "preference": "App\\Middleware\\CustomMiddleware",
            "priority": 100
        }
    }
}
```

**Note:** All middleware is provided by your application - this package only provides the foundation and middleware pipeline infrastructure.

For routing functionality, see the [Router Documentation](docs/router.md) (optional feature).

### Custom Middleware

Create your own middleware to handle requests:

```php
<?php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CustomMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request, 
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Process request
        
        return $handler->handle($request);
    }
}
```

**Important:** Define middleware in configuration (preferred) rather than using `addMiddleware()` method. Configuration-based approach allows for better DI integration and lazy initialization.

## 📚 Documentation

- **[Installation Guide](docs/installation.md)** - Detailed installation and setup
- **[Configuration](docs/configuration.md)** - Configuration system reference (see also [Config Package](https://github.com/Concept-Labs/config))
- **[Middleware](docs/middleware.md)** - Middleware pipeline and lazy initialization
- **[Dependency Injection](docs/dependency-injection.md)** - DI container integration
- **[Router (Optional)](docs/router.md)** - Built-in routing middleware (optional feature)
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
│  • Manages middleware stack with lazy wrappers      │
│  • Processes HTTP requests through pipeline         │
│  • Handles response generation                      │
└──────────────────┬──────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────┐
│              Middleware Pipeline                     │
│  ┌──────────────┐  ┌──────────────┐                │
│  │ Your Custom  │→ │ Your Handler │                │
│  │  Middleware  │  │  (Optional)  │                │
│  └──────────────┘  └──────────────┘                │
│                                                      │
│  Note: All middleware is developer-provided         │
│  This package provides infrastructure only          │
└─────────────────────────────────────────────────────┘
```

### Lazy Middleware Initialization

Middleware uses a **wrapper pattern** for lazy initialization:
- Middleware is not instantiated until actually needed in the request chain
- If a middleware returns a response early, subsequent middleware is never created
- Improves performance by avoiding unnecessary object creation
- Automatic through the MiddlewareWrapper system

See [Middleware Guide](docs/middleware.md) for details.

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
- Leverage configuration **directives** (@import, @include)
- Use **context variables** with `${VAR}` syntax and **environment variables** with `@env()` directive

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

**Q: What makes this different from other PHP frameworks?**  
A: This is not a framework - it's a low-level HTTP application ecosystem. It provides the foundation (middleware pipeline, DI integration, configuration system) that higher-level packages build upon. Your application is entirely configuration-driven.

**Q: Do I need to use the Router?**  
A: No! The router is an optional middleware component included in this package. You can build applications without it or use your own routing solution.

**Q: Where's the Auth middleware, CORS, etc.?**  
A: This package provides only the infrastructure. All middleware (auth, CORS, validation, etc.) is developer-provided. This keeps the package lean and flexible.

**Q: Configuration vs addMiddleware()?**  
A: Configuration is preferred. It enables:
- Lazy initialization through middleware wrappers
- Better DI integration (can use interface IDs as preferences)
- Configuration-driven application building
- The `addMiddleware()` method is available for runtime scenarios

**Q: Is it production-ready?**  
A: Yes! The package is fully tested, follows PSR standards, and SOLID principles. It's designed as a foundation for production applications.

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
