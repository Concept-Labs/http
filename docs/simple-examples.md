# Simple Application Examples

## Example 1: JSON API Server

A minimal JSON API that responds to all requests with JSON data.

### Structure
```
json-api/
├── config/
│   └── app.json
├── src/
│   └── Middleware/
│       └── JsonResponder.php
├── public/
│   └── index.php
└── composer.json
```

### Configuration: `config/app.json`

```json
{
    "app": {
        "name": "JSON API Server"
    },
    "middleware": {
        "json": {
            "preference": "App\\Middleware\\JsonResponder",
            "priority": 100
        }
    }
}
```

### Middleware: `src/Middleware/JsonResponder.php`

```php
<?php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class JsonResponder implements MiddlewareInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory
    ) {}
    
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $data = [
            'message' => 'Hello from JSON API',
            'method' => $request->getMethod(),
            'path' => $request->getUri()->getPath(),
            'timestamp' => date('c')
        ];
        
        $response = $this->responseFactory->createResponse(200);
        $response->getBody()->write(json_encode($data));
        
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}
```

### Entry Point: `public/index.php`

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Concept\Http\Bootstrap;

$app = (new Bootstrap(
    base: dirname(__DIR__),
    configSource: 'config/*.json'
))->app();

$app->run();
```

### DI Configuration: `concept.json`

```json
{
    "singularity": {
        "package": {
            "your-vendor/json-api": {
                "preference": {
                    "App\\Middleware\\JsonResponder": {
                        "class": "App\\Middleware\\JsonResponder"
                    }
                }
            }
        }
    }
}
```

**That's it!** Run with: `php -S localhost:8000 -t public/`

---

## Example 2: Health Check Service

A simple health check service with multiple endpoints.

### Structure
```
health-check/
├── config/
│   └── app.json
├── src/
│   ├── Middleware/
│   │   └── HealthCheck.php
│   └── Service/
│       └── StatusChecker.php
├── public/
│   └── index.php
└── composer.json
```

### Configuration: `config/app.json`

```json
{
    "app": {
        "name": "Health Check Service"
    },
    "middleware": {
        "health": {
            "preference": "App\\Middleware\\HealthCheck",
            "priority": 100,
            "config": {
                "checks": ["database", "redis", "filesystem"]
            }
        }
    }
}
```

### Middleware: `src/Middleware/HealthCheck.php`

```php
<?php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Concept\Config\Contract\ConfigurableInterface;
use Concept\Config\Contract\ConfigurableTrait;
use App\Service\StatusChecker;

class HealthCheck implements MiddlewareInterface, ConfigurableInterface
{
    use ConfigurableTrait;
    
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private StatusChecker $checker
    ) {}
    
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $path = $request->getUri()->getPath();
        
        // Quick health check
        if ($path === '/health') {
            return $this->quickCheck();
        }
        
        // Detailed health check
        if ($path === '/health/detailed') {
            return $this->detailedCheck();
        }
        
        // Pass to next middleware
        return $handler->handle($request);
    }
    
    private function quickCheck(): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(200);
        $response->getBody()->write(json_encode(['status' => 'ok']));
        
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    private function detailedCheck(): ResponseInterface
    {
        $checks = $this->getConfig()->get('checks', []);
        $results = [];
        $allOk = true;
        
        foreach ($checks as $check) {
            $status = $this->checker->check($check);
            $results[$check] = $status;
            if (!$status['ok']) {
                $allOk = false;
            }
        }
        
        $statusCode = $allOk ? 200 : 503;
        $response = $this->responseFactory->createResponse($statusCode);
        $response->getBody()->write(json_encode([
            'status' => $allOk ? 'healthy' : 'unhealthy',
            'checks' => $results,
            'timestamp' => date('c')
        ]));
        
        return $response->withHeader('Content-Type', 'application/json');
    }
}
```

### Service: `src/Service/StatusChecker.php`

```php
<?php
namespace App\Service;

class StatusChecker
{
    public function check(string $service): array
    {
        return match($service) {
            'database' => ['ok' => true, 'latency_ms' => 5],
            'redis' => ['ok' => true, 'latency_ms' => 2],
            'filesystem' => ['ok' => is_writable('/tmp'), 'writable' => true],
            default => ['ok' => false, 'error' => 'Unknown service']
        };
    }
}
```

### Entry Point: `public/index.php`

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Concept\Http\Bootstrap;

$app = (new Bootstrap(
    base: dirname(__DIR__),
    configSource: 'config/*.json'
))->app();

$app->run();
```

### DI Configuration: `concept.json`

```json
{
    "singularity": {
        "package": {
            "your-vendor/health-check": {
                "preference": {
                    "App\\Middleware\\HealthCheck": {
                        "class": "App\\Middleware\\HealthCheck"
                    },
                    "App\\Service\\StatusChecker": {
                        "class": "App\\Service\\StatusChecker",
                        "shared": true
                    }
                }
            }
        }
    }
}
```

**Usage:**
- `curl http://localhost:8000/health` - Quick check
- `curl http://localhost:8000/health/detailed` - Detailed check

---

## Key Takeaways

Both examples demonstrate:

1. **Configuration-Driven**: Application behavior defined in JSON configuration
2. **Minimal Code**: Clean, focused implementations
3. **PSR Compliance**: Using PSR-7 and PSR-15 standards
4. **DI Integration**: Dependencies injected via Singularity
5. **Lazy Initialization**: Middleware only created when needed
6. **No Router Needed**: Simple middleware can handle all requests

These are low-level foundations. For more complex applications with routing, validation, etc., build upon these patterns or use higher-level packages like [simple-http](https://github.com/Concept-Labs/simple-http).
