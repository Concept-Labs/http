# Middleware Guide

## Overview

Middleware provides the core request/response processing mechanism in the Concept-Labs HTTP ecosystem. Each middleware can:
- Inspect and modify requests
- Generate responses
- Pass control to the next middleware
- Execute logic before and after the request is handled

## Lazy Initialization

**Important Feature:** Middleware uses a wrapper pattern for lazy initialization:

1. Middleware is **not instantiated** when the application starts
2. Only created when actually needed during request processing
3. If middleware returns a response early, subsequent middleware is **never created**
4. This improves performance and reduces memory usage

The `MiddlewareWrapper` class handles this automatically - you just configure your middleware preferences.

## Middleware Pipeline

```
Request → Middleware 1 → Middleware 2 → Middleware 3 → Handler → Response
            ↓               ↓               ↓
        (before)        (before)        (before)
            ↑               ↑               ↑
         (after)         (after)         (after)
```

## Creating Middleware

### Basic Middleware

```php
<?php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoggingMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Before request is handled
        $startTime = microtime(true);
        
        // Process request
        $response = $handler->handle($request);
        
        // After request is handled
        $duration = microtime(true) - $startTime;
        error_log("Request processed in {$duration}s");
        
        return $response;
    }
}
```

### Middleware with Dependencies

```php
<?php
namespace App\Middleware;

use App\Service\AuthService;

class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AuthService $authService
    ) {}
    
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $token = $request->getHeaderLine('Authorization');
        
        if (!$this->authService->validate($token)) {
            throw new UnauthorizedException('Invalid token');
        }
        
        $user = $this->authService->getUserFromToken($token);
        $request = $request->withAttribute('user', $user);
        
        return $handler->handle($request);
    }
}
```

## Configuring Middleware

### Global Middleware

Add to all requests via configuration:

```php
// config/app.php
return [
    'middleware' => [
        [
            'preference' => 'App\\Middleware\\CorsMiddleware',
            'priority' => 200,  // Executed first
        ],
        [
            'preference' => 'App\\Middleware\\LoggingMiddleware',
            'priority' => 150,
        ],
        [
            'preference' => 'Concept\\Http\\Router\\RouterInterface',
            'priority' => 100,  // Router middleware
        ],
        [
            'preference' => 'App\\Middleware\\ErrorHandlerMiddleware',
            'priority' => 50,
        ],
    ],
];
```

### Runtime Middleware (Less Preferred)

You can add middleware at runtime, but configuration is preferred:

```php
$app->addMiddleware(
    new App\Middleware\CustomMiddleware(),
    priority: 120,
    id: 'custom-middleware'
);
```

**Use configuration instead for:**
- Lazy initialization benefits
- Better DI integration
- Configuration-driven architecture

### Route-Specific Middleware

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
                        "priority": 100,
                        "config": {
                            "role": "admin"
                        }
                    }
                }
            }
        }
    }
}
```

## Middleware Priority

Higher priority = executed earlier:

```
Priority 200: CORS
Priority 150: Logging
Priority 100: Router
Priority 50:  Error Handler
Priority 0:   Final Handler
```

Same priority preserves order of definition.

## Common Middleware Patterns

### CORS Middleware

```php
class CorsMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $response = $handler->handle($request);
        
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
}
```

### Content Negotiation

```php
class ContentNegotiationMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $accept = $request->getHeaderLine('Accept');
        $request = $request->withAttribute('accept', $accept);
        
        $response = $handler->handle($request);
        
        // Add appropriate Content-Type header
        if (!$response->hasHeader('Content-Type')) {
            $response = $response->withHeader('Content-Type', 'application/json');
        }
        
        return $response;
    }
}
```

### Rate Limiting

```php
class RateLimitMiddleware implements MiddlewareInterface
{
    public function __construct(
        private CacheInterface $cache,
        private int $maxRequests = 100,
        private int $window = 60
    ) {}
    
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $ip = $request->getServerParams()['REMOTE_ADDR'];
        $key = "rate_limit:{$ip}";
        
        $current = (int) $this->cache->get($key, 0);
        
        if ($current >= $this->maxRequests) {
            throw new TooManyRequestsException('Rate limit exceeded');
        }
        
        $this->cache->set($key, $current + 1, $this->window);
        
        return $handler->handle($request);
    }
}
```

### Request Validation

```php
class ValidationMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $body = json_decode((string) $request->getBody(), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new BadRequestException('Invalid JSON');
        }
        
        $request = $request->withParsedBody($body);
        
        return $handler->handle($request);
    }
}
```

## Middleware with Configuration

```php
use Concept\Config\Contract\ConfigurableInterface;
use Concept\Config\Contract\ConfigurableTrait;

class AuthMiddleware implements MiddlewareInterface, ConfigurableInterface
{
    use ConfigurableTrait;
    
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $requiredRole = $this->getConfig()->get('role', 'user');
        
        // Validate user role
        // ...
        
        return $handler->handle($request);
    }
}
```

## Middleware Best Practices

1. **Single Responsibility**: Each middleware should do one thing
2. **Order Matters**: Consider middleware execution order
3. **Performance**: Keep middleware lightweight
4. **Error Handling**: Implement proper error handling
5. **Testing**: Write tests for middleware logic
6. **Reusability**: Design middleware to be reusable

## Testing Middleware

```php
use Tests\TestCase;
use Mockery;

test('auth middleware validates token', function () {
    $authService = Mockery::mock(AuthService::class);
    $authService->shouldReceive('validate')->with('valid-token')->andReturn(true);
    $authService->shouldReceive('getUserFromToken')->andReturn(['id' => 1]);
    
    $middleware = new AuthMiddleware($authService);
    $request = Mockery::mock(ServerRequestInterface::class);
    $handler = Mockery::mock(RequestHandlerInterface::class);
    
    $request->shouldReceive('getHeaderLine')->with('Authorization')->andReturn('valid-token');
    $request->shouldReceive('withAttribute')->andReturnSelf();
    $handler->shouldReceive('handle')->andReturn(Mockery::mock(ResponseInterface::class));
    
    $response = $middleware->process($request, $handler);
    
    expect($response)->toBeInstanceOf(ResponseInterface::class);
});
```

---

[← Routing](routing.md) | [Back to README](../README.md) | [Dependency Injection →](dependency-injection.md)
