# Installation Guide

## System Requirements

### PHP Version
- **Minimum**: PHP 8.2
- **Recommended**: PHP 8.3 or higher

### Required Extensions
- `json` - JSON support
- `mbstring` - Multibyte string support
- `fileinfo` - File type detection

### Optional Extensions
- `opcache` - Improved performance
- `apcu` - Advanced caching capabilities

## Installation Methods

### Method 1: Composer (Recommended)

#### Install in New Project

```bash
# Create project directory
mkdir my-http-app
cd my-http-app

# Initialize composer
composer init

# Require the package
composer require concept-labs/http
```

#### Install in Existing Project

```bash
composer require concept-labs/http
```

## Basic Setup

### 1. Create Entry Point

Create `public/index.php`:

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Concept\Http\Bootstrap;

$bootstrap = new Bootstrap(
    base: dirname(__DIR__),
    configSource: 'config/*.php'
);

$app = $bootstrap->app();
$app->run();
```

### 2. Create Configuration

Create `config/app.php`:

```php
<?php
return [
    'app' => [
        'name' => 'My Application',
    ],
    
    'middleware' => [
        [
            'preference' => 'Concept\\Http\\Router\\RouterInterface',
            'priority' => 100,
        ],
    ],
    
    'routes' => [
        '/' => [
            'method' => 'GET',
            'handler' => [
                'preference' => 'App\\Handler\\HomeHandler',
            ],
        ],
    ],
];
```

## Next Steps

- Read the [Configuration Guide](configuration.md)
- Learn about [Routing](routing.md)
- Explore [Middleware](middleware.md)

---

[Back to README](../README.md) | [Next: Configuration →](configuration.md)
