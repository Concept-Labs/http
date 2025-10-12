# Configuration Guide

## Overview

The Concept-Labs HTTP framework uses a powerful configuration system that supports:
- File globbing for loading multiple config files
- Environment variable substitution
- Configuration inheritance and imports
- Plugin-based parsing
- Context variables

## Configuration Structure

### Application Configuration

Configuration files use JSON format:

```json
{
    "app": {
        "name": "My Application",
        "env": "${APP_ENV|production}",
        "debug": "${APP_DEBUG|false}"
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

Use `${VAR_NAME}` syntax with optional defaults:

```json
{
    "database": {
        "host": "${DB_HOST|localhost}",
        "port": "${DB_PORT|3306}",
        "name": "${DB_NAME}"
    }
}
```

## Context Variables

Built-in context variables:
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
