<?php
namespace Concept\Http\Router;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SimpleRouter implements MiddlewareInterface
{

    private array $handlers = [];

    public function __construct(private RouterInterface $router)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        foreach ($this->handlers as $route) {
            if ($route->match($request)) {
                return $route->handle($request);
            }
        }

        /**
         * Continue to the next middleware
         */
        return $handler->handle($request);
    }

    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    public function addHandler(string $method, string $path, callable $handler): void
    {
        
    }
}