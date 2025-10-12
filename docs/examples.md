# Examples

## Complete Application Example

This example shows a complete REST API application with authentication.

### Project Structure

```
api-app/
├── config/
│   ├── app.php
│   └── routes.php
├── public/
│   └── index.php
├── src/
│   ├── Handler/
│   │   ├── AuthHandler.php
│   │   ├── UserHandler.php
│   │   └── UserCollectionHandler.php
│   ├── Middleware/
│   │   ├── AuthMiddleware.php
│   │   ├── CorsMiddleware.php
│   │   └── JsonMiddleware.php
│   ├── Repository/
│   │   ├── UserRepository.php
│   │   └── UserRepositoryInterface.php
│   └── Service/
│       └── AuthService.php
├── composer.json
└── concept.json
```

### Entry Point

```php
<?php
// public/index.php
require_once __DIR__ . '/../vendor/autoload.php';

use Concept\Http\Bootstrap;

$bootstrap = new Bootstrap(
    base: dirname(__DIR__),
    configSource: 'config/*.json'
);

$app = $bootstrap->app();
$app->run();
```

### Configuration

File: `config/app.json`
```json
{
    "app": {
        "name": "API Application",
        "env": "@env(APP_ENV|production)"
    },
    
    "middleware": {
        "cors": {
            "preference": "App\\Middleware\\CorsMiddleware",
            "priority": 200
        },
        "json": {
            "preference": "App\\Middleware\\JsonMiddleware",
            "priority": 150
        },
        "router": {
            "preference": "Concept\\Http\\Router\\RouterInterface",
            "priority": 100
        }
    }
}
```

File: `config/routes.json`
```json
{
    "route": {
        "/api/auth/login": {
            "method": ["POST"],
            "handler": {
                "preference": "App\\Handler\\AuthHandler"
            }
        },
        "/api/users": {
            "method": ["GET", "POST"],
            "handler": {
                "preference": "App\\Handler\\UserCollectionHandler",
                "middleware": {
                    "auth": {
                        "preference": "App\\Middleware\\AuthMiddleware",
                        "priority": 100
                    }
                }
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
```

### Handlers

```php
<?php
// src/Handler/AuthHandler.php
namespace App\Handler;

use App\Service\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class AuthHandler implements RequestHandlerInterface
{
    public function __construct(
        private AuthService $authService,
        private ResponseFactoryInterface $responseFactory
    ) {}
    
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $username = $body['username'] ?? null;
        $password = $body['password'] ?? null;
        
        if (!$username || !$password) {
            $response = $this->responseFactory->createResponse(400);
            $response->getBody()->write(json_encode([
                'error' => 'Missing credentials'
            ]));
            return $response;
        }
        
        $token = $this->authService->login($username, $password);
        
        if (!$token) {
            $response = $this->responseFactory->createResponse(401);
            $response->getBody()->write(json_encode([
                'error' => 'Invalid credentials'
            ]));
            return $response;
        }
        
        $response = $this->responseFactory->createResponse(200);
        $response->getBody()->write(json_encode([
            'token' => $token
        ]));
        
        return $response;
    }
}
```

```php
<?php
// src/Handler/UserHandler.php
namespace App\Handler;

use App\Repository\UserRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class UserHandler implements RequestHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private ResponseFactoryInterface $responseFactory
    ) {}
    
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
        $id = (int) $request->getAttribute('id');
        $user = $this->userRepository->find($id);
        
        if (!$user) {
            $response = $this->responseFactory->createResponse(404);
            $response->getBody()->write(json_encode([
                'error' => 'User not found'
            ]));
            return $response;
        }
        
        $response = $this->responseFactory->createResponse(200);
        $response->getBody()->write(json_encode($user));
        
        return $response;
    }
    
    private function update(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $request->getAttribute('id');
        $data = $request->getParsedBody();
        
        $user = $this->userRepository->update($id, $data);
        
        $response = $this->responseFactory->createResponse(200);
        $response->getBody()->write(json_encode($user));
        
        return $response;
    }
    
    private function delete(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $request->getAttribute('id');
        $this->userRepository->delete($id);
        
        return $this->responseFactory->createResponse(204);
    }
}
```

### Middleware

```php
<?php
// src/Middleware/CorsMiddleware.php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if ($request->getMethod() === 'OPTIONS') {
            $response = $handler->handle($request);
            return $response->withStatus(200);
        }
        
        $response = $handler->handle($request);
        
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
}
```

```php
<?php
// src/Middleware/AuthMiddleware.php
namespace App\Middleware;

use App\Service\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AuthService $authService,
        private ResponseFactoryInterface $responseFactory
    ) {}
    
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $token = $request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $token);
        
        if (!$token || !$this->authService->validateToken($token)) {
            $response = $this->responseFactory->createResponse(401);
            $response->getBody()->write(json_encode([
                'error' => 'Unauthorized'
            ]));
            return $response;
        }
        
        $user = $this->authService->getUserFromToken($token);
        $request = $request->withAttribute('user', $user);
        
        return $handler->handle($request);
    }
}
```

```php
<?php
// src/Middleware/JsonMiddleware.php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class JsonMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $contentType = $request->getHeaderLine('Content-Type');
        
        if (str_contains($contentType, 'application/json')) {
            $body = json_decode((string) $request->getBody(), true);
            $request = $request->withParsedBody($body);
        }
        
        $response = $handler->handle($request);
        
        if (!$response->hasHeader('Content-Type')) {
            $response = $response->withHeader('Content-Type', 'application/json');
        }
        
        return $response;
    }
}
```

### DI Configuration

File: `concept.json`
```json
{
    "singularity": {
        "package": {
            "your-vendor/api-app": {
                "preference": {
                    "App\\Repository\\UserRepositoryInterface": {
                        "class": "App\\Repository\\UserRepository",
                        "shared": true
                    },
                    "App\\Service\\AuthService": {
                        "class": "App\\Service\\AuthService",
                        "shared": true
                    }
                }
            }
        }
    }
}
```

## Usage Examples

### Making Requests

```bash
# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username": "john", "password": "secret"}'

# Get user (with authentication)
curl http://localhost:8000/api/users/1 \
  -H "Authorization: Bearer <token>"

# Update user
curl -X PUT http://localhost:8000/api/users/1 \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"name": "John Doe", "email": "john@example.com"}'

# Delete user
curl -X DELETE http://localhost:8000/api/users/1 \
  -H "Authorization: Bearer <token>"
```

---

[← Dependency Injection](dependency-injection.md) | [Back to README](../README.md) | [API Reference →](api-reference.md)
