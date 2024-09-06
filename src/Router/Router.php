<?php

namespace Concept\Http\Router;

use Concept\Config\ConfigInterface;
use Concept\Factory\FactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Concept\Http\Router\Route\RouteInterface;
use PSpell\Config;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Class Router
 * @package Concept\Http\Router
 */
class Router implements RouterInterface, MiddlewareInterface, RequestHandlerInterface
{
    /**
     * @var array<RouteInterface>
     */
    protected array $routes = [];

    protected FactoryInterface $factory;

    /**
     * @var ConfigInterface|null
     */
    protected ?ConfigInterface $config = null;

    /**
     * @var ResponseFactoryInterface|null
     */
    protected ?ResponseFactoryInterface $responseFactory = null;

    /**
     * @var RouteInterface|null
     */
    protected ?RouteInterface $routePrototype = null;

    /**
     * @var RequestHandlerInterface|null
     */
    protected ?RequestHandlerInterface $notFoundHandler = null;

    /**
     * Dependency injection for RouteInterface
     *
     * @param RouteInterface $route
     */
    public function __construct(FactoryInterface $factory, RouteInterface $routePrototype)
    {
        $this->factory = $factory;
        $this->routePrototype = $routePrototype;
        $this->initRoutes();
    }

    /**
     * Set the configuration
     *
     * @param ConfigInterface $config
     * @return self
     */
    public function withConfig(ConfigInterface $config): self
    {
        $new = clone $this;
        $new->config = $config;

        return $new;
    }

    /**
     * Set the response factory
     *
     * @param ResponseFactoryInterface $responseFactory
     * @return self
     */
    public function withResponseFactory(ResponseFactoryInterface $responseFactory): self
    {
        $new = clone $this;
        $new->responseFactory = $responseFactory;

        return $new;
    }

    /**
     * Set the not found handler
     *
     * @param RequestHandlerInterface $handler
     * @return self
     */
    public function withNotFoundHandler(RequestHandlerInterface $handler): self
    {
        $new = clone $this;
        $new->notFoundHandler = $handler;

        return $new;
    }

    /**
     * Initialize the routes
     *
     * @return void
     */
    protected function initRoutes(): void
    {
        if ($this->config === null) {
            return;
        }

        $configPath = sprintf('%s.%s', self::CONFIG_NODE, self::CONFIG_NODE_HANDLER);
        $routesData = $this->getConfig()->get($configPath);

        if ($routesData === null || !is_array($routesData)) {
            return;
        }

        foreach ($routesData as $path => $route) {
            $this->addRoute(
                $route['method'], 
                $path,
            );
        }

    }

    public function addRoute(RouteInterface $route): self
    {
        $this->routes[] = $route;

        return $this;
    }
   
    /**
     * Add a route
     *
     * @param string $method
     * @param string $path
     * @param RequestHandlerInterface $handler
     * @return void
     */
    public function addRouteHandler(string $method, string $path, RequestHandlerInterface $handler): self
    {
        $route = $this->getRouteInstance()
            ->withMethod($method)
            ->withPath($path)
            ->withHandler($handler);

        $this->routes[] = $route;

        return $this;
    }

    /**
     * Dispatch the request
     * Meet the requirements of the RequestHandlerInterface
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        return $this->handle($request);
    }

    /**
     * Process the request
     * Meet the requirements of the MiddlewareInterface
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * 
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->handle($request);
    }

    /**
     * Handle the request
     * Meet the requirements of the RequestHandlerInterface
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        foreach ($this->getRoutes() as $route) {

            $route->createHandler($this->factory);

            if ($route->match($request)) {
                return $route->dispatch($request);
            }
        }

        return $this->handleNotFound($request);
    }

    /**
     * Get the routes
     *
     * @return RouteInterface[]
     */
    protected function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Get the response factory
     *
     * @return ResponseFactoryInterface
     * @throws \RuntimeException
     */
    protected function getResponseFactory(): ResponseFactoryInterface
    {
        if ($this->responseFactory === null) {
            throw new \RuntimeException('Response factory is not set');
        }

        return $this->responseFactory;
    }

    /**
     * Get the route instance
     *
     * @return RouteInterface
     * @throws \RuntimeException
     */
    protected function getRouteInstance(): RouteInterface
    {
        return clone $this->routePrototype;
    }

    /**
     * Get the configuration
     *
     * @return ConfigInterface|null
     */
    protected function getConfig(): ?ConfigInterface
    {
        return $this->config;
    }

    
    protected function getFactory(): FactoryInterface
    {
        return $this->factory;
    }

    /**
     * Get the 404 handler
     *
     * @return RequestHandlerInterface|null
     */
    protected function get404Handler(): ?RequestHandlerInterface
    {
        return $this->notFoundHandler;
    }

    /**
     * Handle not found routes
     * 
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    protected function handleNotFound(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->get404Handler() !== null) {
            return $this->get404Handler()->handle($request);
        }

        $response = $this->getResponseFactory()->createResponse(404);
        $response->getBody()->write('Not Found');

        return $response;
    }
}
