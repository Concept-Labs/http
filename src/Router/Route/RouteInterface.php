<?php

namespace Concept\Http\Router\Route;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface RouteInterface
{
    /**
     * Match the route
     * 
     * @param ServerRequestInterface $request
     * 
     * @return bool
     */
    public function match(ServerRequestInterface $request): bool;

    /**
     * Dispatch the route
     * 
     * @param ServerRequestInterface $request
     * 
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface;

    /**
     * Set the method
     * 
     * @param string $method
     * 
     * @return self
     */
    public function withMethod(string $method): self;

    /**
     * Set the path
     * 
     * @param string $path
     * 
     * @return self
     */
    public function withPath(string $path): self;


    /**
     * Set the handler
     * 
     * @param RequestHandlerInterface $handler
     * 
     * @return self
     */
    public function withHandler(RequestHandlerInterface $handler): self;

    /**
     * Set the handler factory
     * 
     * @param callable $factory
     * 
     * @return self
     */
    public function withHandlerFactory(callable $factory): self;
}
