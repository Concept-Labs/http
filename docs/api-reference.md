# API Reference

## Core Classes

### Bootstrap

Main entry point for application initialization.

```php
class Bootstrap
```

**Constructor Parameters:**
- `string $base` - Base path of the application (required)
- `string $configSource` - Configuration source path or glob pattern (required)

**Methods:**

#### app(): AppInterface

Creates and returns the application instance.

```php
public function app(): AppInterface
```

**Example:**
```php
$bootstrap = new Bootstrap(
    base: __DIR__,
    configSource: 'config/*.json'
);
$app = $bootstrap->app();
```

---

### HttpApp

Main HTTP application class implementing PSR-15 request handling.

```php
class HttpApp extends AbstractApp implements AppInterface
```

**Constructor Dependencies:**
- `AppConfigInterface $config` - Application configuration
- `ServerRequestFactoryInterface $serverRequestFactory` - PSR-17 server request factory
- `ResponseFactoryInterface $responseFactory` - PSR-17 response factory
- `MiddlewareStackHandlerInterface $middlewareStackHandler` - Middleware stack handler

**Methods:**

#### run(): void

Runs the application and processes the middleware stack.

```php
public function run(): void
```

#### addMiddleware(): static

Adds middleware to the application stack.

```php
public function addMiddleware(
    MiddlewareInterface $middleware,
    int $priority = 100,
    ?string $id = null
): static
```

**Parameters:**
- `MiddlewareInterface $middleware` - The middleware instance
- `int $priority` - Priority (higher = executed first)
- `?string $id` - Optional middleware identifier

#### withServerRequest(): static

Returns a clone with the specified server request.

```php
public function withServerRequest(ServerRequestInterface $request): static
```

#### getConfig(): AppConfigInterface

Returns the application configuration.

```php
public function getConfig(): AppConfigInterface
```

---

### Router

PSR-15 middleware for routing requests to handlers.

```php
class Router implements RouterInterface, SharedInterface
```

**Methods:**

#### process(): ResponseInterface

Processes the request through the routing system.

```php
public function process(
    ServerRequestInterface $request,
    RequestHandlerInterface $handler
): ResponseInterface
```

---

### Route

Represents a single route with matching and handling logic.

```php
class Route implements RouteInterface
```

**Methods:**

#### match(): bool

Checks if the route matches the request.

```php
public function match(ServerRequestInterface $request): bool
```

#### handle(): ResponseInterface

Handles the matched request.

```php
public function handle(ServerRequestInterface $request): ResponseInterface
```

**Configuration:**

```json
{
    "route": {
        "/users/{id}": {
            "method": ["GET", "PUT"],
            "handler": {
                "preference": "App\\Handler\\UserHandler"
            }
        }
    }
}
```

---

## Middleware Components

### MiddlewareAggregator

Aggregates and manages middleware with priority ordering.

```php
class MiddlewareAggregator implements MiddlewareAggregatorInterface
```

**Constructor Dependencies:**
- `MiddlewareWrapperInterface $middlewareWrapperPrototype` - Prototype for wrapping middleware
- `AppConfigInterface $appConfig` - Application configuration

**Iteration:**

```php
foreach ($aggregator as $middleware) {
    // $middleware is MiddlewareWrapperInterface
}
```

---

### MiddlewareWrapper

Wraps middleware for lazy instantiation and configuration.

```php
class MiddlewareWrapper implements MiddlewareWrapperInterface, PrototypeInterface
```

**Methods:**

#### process(): ResponseInterface

Processes the request through the wrapped middleware.

```php
public function process(
    ServerRequestInterface $request,
    RequestHandlerInterface $handler
): ResponseInterface
```

#### prototype(): static

Creates a new prototype instance.

```php
public function prototype(): static
```

---

### MiddlewareStackHandler

Handles the middleware stack execution.

```php
class MiddlewareStackHandler implements MiddlewareStackHandlerInterface
```

**Methods:**

#### withFinalHandler(): static

Returns a clone with the specified final handler.

```php
public function withFinalHandler(RequestHandlerInterface $handler): static
```

#### addMiddleware(): static

Adds middleware to the stack.

```php
public function addMiddleware(MiddlewareInterface $middleware): static
```

#### handle(): ResponseInterface

Processes the request through the middleware stack.

```php
public function handle(ServerRequestInterface $request): ResponseInterface
```

---

## Configuration

### AppConfig

Main configuration class for the application.

```php
class AppConfig extends Config implements AppConfigInterface, SharedInterface
```

**Extends:** `Concept\Config\Config`

**Configuration Structure:**

```json
{
    "app": {},
    "middleware": {}
}
```

---

## Interfaces

### AppInterface

```php
interface AppInterface
{
    public function run(): void;
    public function addMiddleware(
        MiddlewareInterface $middleware,
        int $priority = 100,
        ?string $id = null
    ): static;
    public function withServerRequest(ServerRequestInterface $request): static;
}
```

### AppFactoryInterface

```php
interface AppFactoryInterface
{
    public function create(array $args = []): AppInterface;
}
```

### RouterInterface

```php
interface RouterInterface extends MiddlewareInterface
{
    const CONFIG_ROUTE_NODE = 'routes';
}
```

### RouteInterface

```php
interface RouteInterface
{
    const CONFIG_PATH_NODE = 'path';
    const CONFIG_METHOD_NODE = 'method';
    const CONFIG_HANDLER_NODE = 'handler';
    
    public function match(ServerRequestInterface $request): bool;
    public function handle(ServerRequestInterface $request): ResponseInterface;
}
```

### MiddlewareAggregatorInterface

```php
interface MiddlewareAggregatorInterface extends IteratorAggregate
{
    const CONFIG_NODE_MIDDLEWARE = 'middleware';
}
```

---

## Exception Classes

### HttpException

Base exception for HTTP-related errors.

```php
class HttpException extends \RuntimeException implements HttpExceptionInterface
```

### HttpAppException

Exception specific to application errors.

```php
class HttpAppException extends HttpException implements HttpAppExceptionInterface
```

### Router Exceptions

#### NotFoundException

```php
class NotFoundException extends RouterException
```

Thrown when a route is not found.

#### RouterException

```php
class RouterException extends \RuntimeException implements RouterExceptionInterface
```

Base exception for router errors.

---

## Constants

### Configuration Nodes

```json
// Middleware configuration node
MiddlewareAggregatorInterface::CONFIG_NODE_MIDDLEWARE = "middleware"

// Route configuration node (within router middleware)
RouterInterface::CONFIG_ROUTE_NODE = "route"

// Route path configuration
RouteInterface::CONFIG_PATH_NODE = "path"

// Route method configuration
RouteInterface::CONFIG_METHOD_NODE = "method"

// Route handler configuration
RouteInterface::CONFIG_HANDLER_NODE = "handler"

// Singularity preference node
ConfigNodeInterface::NODE_PREFERENCE = "preference"
```

---

## Type Definitions

### Middleware Configuration

```json
{
    "preference": "string",
    "priority": 100,
    "config": {}
}
```

### Route Configuration

```json
{
    "method": ["GET"],
    "handler": {
        "preference": "string",
        "config": {},
        "middleware": {
            "id": {
                "preference": "string",
                "priority": 100,
                "config": {}
            }
        }
    }
}
```

---

## PSR Compliance

The framework implements the following PSR standards:

- **PSR-4**: Autoloading Standard
- **PSR-7**: HTTP Message Interface
- **PSR-11**: Container Interface
- **PSR-12**: Extended Coding Style Guide
- **PSR-15**: HTTP Server Request Handlers
- **PSR-17**: HTTP Factories

---

[← Examples](examples.md) | [Back to README](../README.md)
