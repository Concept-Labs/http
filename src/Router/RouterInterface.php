<?php
namespace Concept\Http\Router;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Interface RouterInterface
 * @package Concept\Http\Router
 */
interface RouterInterface
{
    const CONFIG_NODE = 'router';
    const CONFIG_NODE_HANDLER = 'handler';

    /**
     * Dispatch the request
     * 
     * @param ServerRequestInterface $request
     * 
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface;

    
    /**
     * Add a route handler factory
     * 
     * @param string $method
     * @param string $path
     * @param callable $factory
     * 
     * @return self
     */
    public function addRouteHandlerFactory(string $method, string $path, callable $factory): self;

    /**
     * Add a route
     * 
     * @param string $method
     * @param string $path
     * @param RequestHandlerInterface $handler
     * 
     * @return self
     */
    public function addRouteHandler(string $method, string $path, RequestHandlerInterface $handler): self;

    /**
     * Set the not found handler
     * 
     * @param RequestHandlerInterface $handler
     * 
     * @return self
     */
    public function withNotFoundHandler(RequestHandlerInterface $handler): self;
}