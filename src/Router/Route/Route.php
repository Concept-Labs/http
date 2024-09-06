<?php

namespace Concept\Http\Router\Route;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Route implements RouteInterface
{
    /**
     * @var string|null
     */
    protected ?string $method = null;

    /**
     * @var string|null
     */
    protected ?string $path = null;

    /**
     * @var RequestHandlerInterface|null
     */
    protected ?RequestHandlerInterface $handler = null;

    /**
     * @var callable
     */
    protected  $handlerFactory;

    /**
     * {@inheritDoc}
     */
    public function match(ServerRequestInterface $request): bool
    {
        /**
         * @todo: Match any method if $this->method is empty?
         */
        if (!empty($this->method) && $this->method !== $request->getMethod()) {
            return false;
        }

        $routePath = preg_replace('/\//', '\/', $this->getPath());
        $routePath = preg_replace('/\{[a-zA-Z0-9_]+\}/', '[a-zA-Z0-9_]+', $routePath);

        return (bool)preg_match('/^' . $routePath . '$/', $request->getUri()->getPath());
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->dispatch($request);
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $request = $this->extractParameters($request);

        return $this->getHandler()->handle($request);
    }

    /**
     * {@inheritDoc}
     */
    public function withMethod(string $method): self
    {
        $new = clone $this;
        $new->method = $method;

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withPath(string $path): self
    {
        $new = clone $this;
        $new->path = $path;

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withHandler(RequestHandlerInterface $handler): self
    {
        $new = clone $this;
        $new->handler = $handler;

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withHandlerFactory(callable $factory): self
    {
        $new = clone $this;
        $new->handlerFactory = $factory;

        return $this;
    }

    /**
     * Get the HTTP method
     * 
     * @return string
     */
    protected function getMethod(): string
    {
        if ($this->method === null) {
            throw new \RuntimeException('Route method is not set');
        }
        
        return $this->method;
    }

    /**
     * Get the route path
     * 
     * @return string
     */
    protected function getPath(): string
    {
        if ($this->path === null) {
            throw new \RuntimeException('Route path is not set');
        }

        return $this->path;
    }

    /**
     * Get the route handler
     * 
     * @return RequestHandlerInterface
     */
    protected function getHandler(): RequestHandlerInterface
    {
        if ($this->handler === null) {
            throw new \RuntimeException('Route handler is not set');
        }

        return $this->handler;
    }

    /**
     * Extract parameters from the request URI
     * 
     * @param ServerRequestInterface $request
     * 
     * @return ServerRequestInterface
     */
    private function extractParameters(ServerRequestInterface $request): ServerRequestInterface
    {
        $routePathParts = explode('/', $this->getPath());
        $requestUriParts = explode('/', $request->getUri()->getPath());
        $parameters = [];

        foreach ($routePathParts as $index => $part) {
            if (preg_match('/\{([a-zA-Z0-9_]+)\}/', $part, $matches)) {
                $parameters[$matches[1]] = $requestUriParts[$index];
            }
        }

        foreach ($parameters as $key => $value) {
            if ($value !== null) {
                $request = $request->withAttribute($key, $value);

                /**
                 * @todo: optionaly add to query params?
                 */
                $queryParams = $request->getQueryParams();
                $queryParams[$key] = $value;
                $request = $request->withQueryParams($queryParams);
            }
        }

        return $request;
    }
}
