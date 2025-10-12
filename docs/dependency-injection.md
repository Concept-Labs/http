# Dependency Injection Guide

## Overview

The Concept-Labs HTTP framework is built on the Singularity DI container, providing powerful dependency injection capabilities with:
- Constructor injection
- Automatic dependency resolution
- Lifecycle management (Shared, Prototype, Weak)
- Configuration-driven bindings

## Basic Concepts

### Service Registration

Services are registered in `concept.json`:

```json
{
    "singularity": {
        "package": {
            "your-vendor/your-package": {
                "preference": {
                    "App\\Repository\\UserRepositoryInterface": {
                        "class": "App\\Repository\\UserRepository"
                    },
                    "App\\Service\\AuthService": {
                        "class": "App\\Service\\AuthService"
                    }
                }
            }
        }
    }
}
```

### Constructor Injection

```php
<?php
namespace App\Handler;

use App\Repository\UserRepositoryInterface;
use App\Service\AuthService;

class UserHandler implements RequestHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private AuthService $authService,
        private ResponseFactoryInterface $responseFactory
    ) {}
    
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute('id');
        $user = $this->userRepository->find($id);
        
        // ...
    }
}
```

## Lifecycle Management

### Shared (Singleton)

Single instance shared across application:

```json
{
    "preference": {
        "App\\Service\\CacheService": {
            "class": "App\\Service\\CacheService",
            "lifecycle": "shared"
        }
    }
}
```

Or implement `SharedInterface`:

```php
use Concept\Singularity\Contract\Lifecycle\SharedInterface;

class CacheService implements SharedInterface
{
    // Single instance
}
```

### Prototype (New Instance)

New instance on each resolution:

```php
use Concept\Singularity\Contract\Lifecycle\PrototypeInterface;

class RequestContext implements PrototypeInterface
{
    // New instance each time
}
```

### Weak (Weak Reference)

Allows garbage collection:

```php
use Concept\Singularity\Contract\Lifecycle\WeakInterface;

class TemporaryCache implements WeakInterface
{
    // Can be garbage collected
}
```

## Injectable Services

Use `InjectableInterface` for automatic dependency injection:

```php
<?php
namespace App\Service;

use Concept\Singularity\Contract\Initialization\InjectableInterface;
use Concept\Singularity\Plugin\Attribute\Injector;

class NotificationService implements InjectableInterface
{
    private MailerInterface $mailer;
    private LoggerInterface $logger;
    
    #[Injector]
    public function depends(
        MailerInterface $mailer,
        LoggerInterface $logger
    ): void {
        $this->mailer = $mailer;
        $this->logger = $logger;
    }
    
    public function send(string $message): void
    {
        $this->mailer->send($message);
        $this->logger->info('Notification sent');
    }
}
```

## Factory Pattern

### Using ServiceFactory

```php
<?php
namespace App\Factory;

use Concept\Singularity\Factory\ServiceFactory;
use App\Handler\UserHandler;

class UserHandlerFactory extends ServiceFactory
{
    public function create(array $args = []): UserHandler
    {
        return $this->createService(UserHandler::class, $args);
    }
}
```

### Factory Interface

```php
{
    "preference": {
        "App\\Factory\\UserHandlerFactoryInterface": {
            "class": "App\\Factory\\UserHandlerFactory"
        }
    }
}
```

## Configuration Injection

### Configurable Services

```php
use Concept\Config\Contract\ConfigurableInterface;
use Concept\Config\Contract\ConfigurableTrait;

class ApiClient implements ConfigurableInterface
{
    use ConfigurableTrait;
    
    public function getBaseUrl(): string
    {
        return $this->getConfig()->get('base_url');
    }
    
    public function getTimeout(): int
    {
        return $this->getConfig()->get('timeout', 30);
    }
}
```

Configuration:

```php
// config/app.php
return [
    'api' => [
        'base_url' => '${API_BASE_URL|https://api.example.com}',
        'timeout' => 60,
    ],
];
```

## Advanced Patterns

### Service Locator Pattern

```php
<?php
use Psr\Container\ContainerInterface;

class ServiceLocator
{
    public function __construct(
        private ContainerInterface $container
    ) {}
    
    public function get(string $id): mixed
    {
        return $this->container->get($id);
    }
}
```

### Decorator Pattern

```php
{
    "preference": {
        "App\\Service\\CachedUserRepository": {
            "class": "App\\Service\\CachedUserRepository",
            "args": {
                "repository": "@App\\Repository\\UserRepository",
                "cache": "@App\\Service\\CacheService"
            }
        }
    }
}
```

```php
class CachedUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private UserRepositoryInterface $repository,
        private CacheInterface $cache
    ) {}
    
    public function find(int $id): ?User
    {
        $key = "user:{$id}";
        
        if ($cached = $this->cache->get($key)) {
            return $cached;
        }
        
        $user = $this->repository->find($id);
        $this->cache->set($key, $user);
        
        return $user;
    }
}
```

## Container Access

### In Handlers

```php
class AdminHandler implements RequestHandlerInterface
{
    public function __construct(
        private ContainerInterface $container
    ) {}
    
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Access service directly
        $service = $this->container->get(SomeService::class);
        
        // ...
    }
}
```

### Bootstrap Container

```php
$bootstrap = new Bootstrap(
    base: __DIR__,
    configSource: 'config/*.php',
    container: $customContainer  // Optional custom PSR-11 container
);
```

## Best Practices

1. **Prefer Constructor Injection**: Explicit dependencies
2. **Use Interfaces**: Program to interfaces, not implementations
3. **Avoid Service Locator**: Use only when necessary
4. **Lifecycle Awareness**: Choose appropriate lifecycle
5. **Configuration**: Externalize configuration
6. **Testing**: Easy to mock dependencies

## Testing with DI

```php
use Tests\TestCase;
use Mockery;

test('handler uses repository', function () {
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('find')->with(1)->andReturn(new User(1, 'John'));
    
    $responseFactory = Mockery::mock(ResponseFactoryInterface::class);
    $authService = Mockery::mock(AuthService::class);
    
    $handler = new UserHandler($repository, $authService, $responseFactory);
    
    // Test handler
});
```

---

[← Middleware](middleware.md) | [Back to README](../README.md) | [Examples →](examples.md)
