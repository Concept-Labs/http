# Routing Guide

## Overview

The routing system provides flexible, pattern-based URL routing with:
- Dynamic route parameters
- Multiple HTTP methods per route
- Route-specific middleware
- RESTful routing support

## Basic Routing

### Simple Route

```php
[
    'routes' => [
        '/' => [
            'method' => 'GET',
            'handler' => [
                'preference' => 'App\\Handler\\HomeHandler',
            ],
        ],
    ],
]
```

### Multiple Methods

```php
'/api/users' => [
    'method' => ['GET', 'POST'],
    'handler' => [
        'preference' => 'App\\Handler\\UserListHandler',
    ],
],
```

## Dynamic Parameters

### Path Parameters

```php
'/users/{id}' => [
    'method' => 'GET',
    'handler' => [
        'preference' => 'App\\Handler\\UserHandler',
    ],
],
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

```php
'/users/{id}/posts/{postId?}' => [
    'method' => 'GET',
    'handler' => [...],
],
```

## RESTful Routes

```php
[
    'routes' => [
        '/api/users' => [
            'method' => ['GET', 'POST'],
            'handler' => [
                'preference' => 'App\\Handler\\UserCollectionHandler',
            ],
        ],
        '/api/users/{id}' => [
            'method' => ['GET', 'PUT', 'DELETE'],
            'handler' => [
                'preference' => 'App\\Handler\\UserResourceHandler',
            ],
        ],
    ],
]
```

## Route Middleware

Add middleware to specific routes:

```php
'/admin/dashboard' => [
    'method' => 'GET',
    'handler' => [
        'preference' => 'App\\Handler\\AdminDashboard',
        'middleware' => [
            'auth' => [
                'preference' => 'App\\Middleware\\AuthMiddleware',
                'priority' => 100,
            ],
            'admin' => [
                'preference' => 'App\\Middleware\\AdminMiddleware',
                'priority' => 90,
            ],
        ],
    ],
],
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

```php
// config/routes-api.php
return [
    'routes' => [
        '/api/v1/users' => [...],
        '/api/v1/posts' => [...],
    ],
];

// config/routes-admin.php
return [
    'routes' => [
        '/admin/dashboard' => [...],
        '/admin/users' => [...],
    ],
];
```

Load both with glob pattern: `config/routes-*.php`

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
