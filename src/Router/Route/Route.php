<?php
namespace Concept\Http\Router\Route;

use Concept\Config\Contract\ConfigurableTrait;
use Concept\Http\Router\Route\Handler\RouteHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Route implements RouteInterface
{

    use ConfigurableTrait;

    public function __construct(private RouteHandlerInterface $routeHandlerPrototype)
    {}


    /**
     * {@inheritDoc}
     */
    /**
     @todo: static
     */
    public function match(ServerRequestInterface $request): bool
    {
        if (!in_array($request->getMethod(), $this->getMethods())) {
            return false;
        }
        
        $routePathRegex = $this->buildRouteRegex($this->getPath());

        return (bool)preg_match($routePathRegex, rtrim($request->getUri()->getPath(), '/'));
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $request = $this->extractParameters($request);

        return $this->getRouteHandlerPrototype()
            ->setConfig($this->getConfig()->node(RouteInterface::CONFIG_HANDLER_NODE))
            ->handle($request);
    }

    /**
     * Build a regex for matching the route with dynamic and optional parameters
     * 
     * @param string $path
     * @return string
     */
    protected function buildRouteRegex(string $path): string
    {
        // Replace dynamic parameters {param} with regular expressions
        $routePath = preg_replace('#\{([a-zA-Z0-9_]+)\}#', '(?P<$1>[a-zA-Z0-9_]+)', $path);

        // Handle optional parameters in the route
        $routePath = preg_replace('#/\{([a-zA-Z0-9_]+)\?\}#', '(/(?P<$1>[a-zA-Z0-9_]+))?', $routePath);

        return '#^' . $routePath . '$#';
    }

    /**
     * Extract parameters from the request URI and inject them into the request
     * 
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    protected function extractParameters(ServerRequestInterface $request): ServerRequestInterface
    {
        $routePath = $this->getPath();
        $path = rtrim($request->getUri()->getPath(), '/');

        // Відповідність параметрів маршруту запиту
        if (preg_match($this->buildRouteRegex($routePath), $path, $matches)) {
            foreach ($matches as $key => $value) {
                if (!is_int($key)) {
                    $request = $request->withAttribute($key, $value);

                    // Додаємо параметри також до запитів, якщо це потрібно
                    $queryParams = $request->getQueryParams();
                    $queryParams[$key] = $value;
                    $request = $request->withQueryParams($queryParams);
                }
            }
        }

        return $request;
    }

    /**
     * Get the HTTP methods (supports multiple methods)
     * 
     * @return array
     */
    protected function getMethods(): array
    {
        if (!$this->getConfig()->has(RouteInterface::CONFIG_METHOD_NODE)) {
            throw new \RuntimeException('Route methods are not set');
        }

        $method =  $this->getConfig()->get(RouteInterface::CONFIG_METHOD_NODE);

        return is_array($method) ? $method : [$method];
    }

    /**
     * Get the route path
     * 
     * @return string
     */
    protected function getPath(): string
    {
        if (!$this->getConfig()->has(RouteInterface::CONFIG_PATH_NODE)) {
            throw new \RuntimeException('Route path is not set');
        }

        return $this->getConfig()->get(RouteInterface::CONFIG_PATH_NODE);
    }

    /**
     * Get the route handler
     * 
     * @return RouteHandlerInterface
     */
    protected function getRouteHandlerPrototype(): RouteHandlerInterface
    {   
        return clone $this->routeHandlerPrototype;
    }
}
