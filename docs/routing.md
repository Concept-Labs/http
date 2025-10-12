# Routing Guide

## Overview

The routing system provides flexible, pattern-based URL routing with:
- Dynamic route parameters
- Multiple HTTP methods per route
- Route-specific middleware
- RESTful routing support

## Basic Routing

### Simple Route

Router is configured as middleware with routes in the "route" subnode:

```json
{
    "middleware": {
        "router": {
            "preference": "Concept\\Http\\Router\\RouterInterface",
            "priority": 10,
            "route": {
                "/": {
                    "method": ["GET"],
                    "handler": {
                        "preference": "App\\Handler\\HomeHandler"
                    }
                }
            }
        }
    }
}
```

### Multiple Methods

```json
{
    "route": {
        "/api/users": {
            "method": ["GET", "POST"],
            "handler": {
                "preference": "App\\Handler\\UserListHandler"
            }
        }
    }
}
```

## Dynamic Parameters

### Path Parameters

```json
{
    "route": {
        "/users/{id}": {
            "method": ["GET"],
            "handler": {
                "preference": "App\\Handler\\UserHandler"
            }
        }
    }
}
```

Access parameters in handler:

```php
public function handle(ServerRequestInterface $request): ResponseInterface
{
    $id = $request->getAttribute('id');
    // or
    $id = $request->getQueryParams()['id'];
    
    // ...
}
```

### Optional Parameters

```json
{
    "route": {
        "/users/{id}/posts/{postId?}": {
            "method": ["GET"],
            "handler": {}
        }
    }
}
```

## RESTful Routes

```json
{
    "middleware": {
        "router": {
            "preference": "Concept\\Http\\Router\\RouterInterface",
            "priority": 10,
            "route": {
                "/api/users": {
                    "method": ["GET", "POST"],
                    "handler": {
                        "preference": "App\\Handler\\UserCollectionHandler"
                    }
                },
                "/api/users/{id}": {
                    "method": ["GET", "PUT", "DELETE"],
                    "handler": {
                        "preference": "App\\Handler\\UserResourceHandler"
                    }
                }
            }
        }
    }
}
```

## Route Middleware

Add middleware to specific routes:

```json
{
    "route": {
        "/admin/dashboard": {
            "method": ["GET"],
            "handler": {
                "preference": "App\\Handler\\AdminDashboard",
                "middleware": {
                    "auth": {
                        "preference": "App\\Middleware\\AuthMiddleware",
                        "priority": 100
                    },
                    "admin": {
                        "preference": "App\\Middleware\\AdminMiddleware",
                        "priority": 90
                    }
                }
            }
        }
    }
}
```

## Route Patterns

The router uses regex patterns:

| Pattern | Matches | Example |
|---------|---------|---------|
| `/users` | Exact path | `/users` |
| `/users/{id}` | Dynamic segment | `/users/123` |
| `/users/{id}/posts/{postId?}` | Optional segment | `/users/123` or `/users/123/posts/456` |

### Pattern Matching Rules

1. Parameters must be alphanumeric with underscores: `{user_id}`
2. Optional parameters use `?`: `{id?}`
3. Paths are matched without trailing slashes
4. First matching route wins

## Creating Route Handlers

### Basic Handler

```php
<?php
namespace App\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class UserHandler implements RequestHandlerInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory
    ) {}
    
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute('id');
        
        $response = $this->responseFactory->createResponse();
        $response->getBody()->write("User ID: $id");
        
        return $response;
    }
}
```

### RESTful Handler

```php
<?php
namespace App\Handler;

class UserResourceHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return match($request->getMethod()) {
            'GET' => $this->show($request),
            'PUT' => $this->update($request),
            'DELETE' => $this->delete($request),
        };
    }
    
    private function show(ServerRequestInterface $request): ResponseInterface
    {
        // Implementation
    }
    
    private function update(ServerRequestInterface $request): ResponseInterface
    {
        // Implementation
    }
    
    private function delete(ServerRequestInterface $request): ResponseInterface
    {
        // Implementation
    }
}
```

## Advanced Routing

### Route Groups (via configuration)

Use @import directive to organize routes:

```json
{
    "middleware": {
        "router": {
            "preference": "Concept\\Http\\Router\\RouterInterface",
            "priority": 10,
            "@import": [
                "${BASE}/config/routes-api.json",
                "${BASE}/config/routes-admin.json"
            ]
        }
    }
}
```

File: `config/routes-api.json`
```json
{
    "route": {
        "/api/v1/users": {},
        "/api/v1/posts": {}
    }
}
```

File: `config/routes-admin.json`
```json
{
    "route": {
        "/admin/dashboard": {},
        "/admin/users": {}
    }
}
```

## Error Handling

### 404 Not Found

When no route matches, the request passes to the next middleware. Configure a final handler:

```php
$app->addMiddleware(
    new NotFoundMiddleware(),
    priority: 0  // Lowest priority
);
```

---

[← Configuration](configuration.md) | [Back to README](../README.md) | [Middleware →](middleware.md)
