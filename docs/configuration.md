# Configuration Guide

## Overview

The Concept-Labs HTTP ecosystem uses a powerful configuration system that enables **building entirely different applications through configuration alone**. See the [Configuration Package](https://github.com/Concept-Labs/config) for complete documentation.

The configuration system supports:
- File globbing for loading multiple config files
- Context variable substitution (`${BASE}`, `${VENDOR}`)
- Environment variable substitution (`@env()`)
- Configuration imports and includes
- Plugin-based parsing

## Configuration-Driven Architecture

The key philosophy: **your application is defined by configuration, not code**. You can build:
- REST APIs
- Web applications
- CLI runners
- Webhook handlers
- Health check services

All through different configuration files using the same codebase.

## Configuration Structure

### Application Configuration

Configuration files use JSON format:

```json
{
    "app": {
        "name": "My Application",
        "env": "@env(APP_ENV|production)",
        "debug": "@env(APP_DEBUG|false)"
    },
    
    "middleware": {
        
    }
}
```

### Configuration Loading

The Bootstrap class loads configuration using glob patterns:

```php
$bootstrap = new Bootstrap(
    base: __DIR__,
    configSource: 'config/*.json'  // Loads all JSON files in config/
);
```

## Environment Variables

Use `@env()` directive with optional defaults:

```json
{
    "database": {
        "host": "@env(DB_HOST|localhost)",
        "port": "@env(DB_PORT|3306)",
        "name": "@env(DB_NAME)"
    }
}
```

## Context Variables

Built-in context variables using `${VAR}` syntax:
- `${BASE}` - Application base path
- `${VENDOR}` - Vendor directory path

```json
{
    "paths": {
        "root": "${BASE}",
        "cache": "${BASE}/var/cache"
    }
}
```

**Note:** The `${VAR}` syntax is for context variables provided by the Context plugin, not environment variables. Use `@env()` directive for environment variables.

## Configuration Nodes

### App Configuration

```json
{
    "app": {
        "name": "Application Name",
        "env": "production",
        "debug": false,
        "timezone": "UTC"
    }
}
```

### Middleware Configuration

```json
{
    "middleware": {
        "auth": {
            "preference": "App\\Middleware\\AuthMiddleware",
            "priority": 100,
            "config": {
                
            }
        },
        "router": {
            "preference": "Concept\\Http\\Router\\RouterInterface",
            "priority": 50
        }
    }
}
```

Priority order:
- Higher priority = executed first
- Same priority = order preserved

### Router Configuration

Router is configured as middleware with routes defined in the "route" subnode:

```json
{
    "middleware": {
        "router": {
            "preference": "Concept\\Http\\Router\\RouterInterface",
            "priority": 10,
            "@import": [
                "${BASE}/etc/router/*.json"
            ],
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
                        "preference": "App\\Handler\\UserHandler",
                        "middleware": {
                            "auth": {
                                "preference": "App\\Middleware\\AuthMiddleware",
                                "priority": 100
                            }
                        }
                    }
                }
            }
        }
    }
}
```

## DI Container Configuration

Configure service bindings in `concept.json`:

```json
{
    "singularity": {
        "package": {
            "your-vendor/your-package": {
                "preference": {
                    "App\\Handler\\HomeHandler": {
                        "class": "App\\Handler\\HomeHandler"
                    },
                    "App\\Repository\\UserRepositoryInterface": {
                        "class": "App\\Repository\\UserRepository",
                        "shared": true
                    }
                }
            }
        }
    }
}
```

## Best Practices

1. **Separate Concerns**: Use multiple configuration files
2. **Environment-Specific**: Use env variables for deployment differences
3. **Type Safety**: Document expected configuration structure
4. **Validation**: Validate configuration at bootstrap
5. **Defaults**: Provide sensible defaults with `|` operator

---

[← Installation](installation.md) | [Back to README](../README.md) | [Routing →](routing.md)
