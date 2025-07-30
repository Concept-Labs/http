<?php
namespace Concept\Http\RequestHandler;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface MiddlewareStackHandlerInterface extends RequestHandlerInterface
{

    /**
     * Set the final handler for the stack
     *
     * @param RequestHandlerInterface $handler
     * @return static
     */
    public function withFinalHandler(RequestHandlerInterface $handler): static;

    /**
     * Add a middleware to the stack
     *
     * @param MiddlewareInterface $middleware
     * @return static
     */
    public function addMiddleware(MiddlewareInterface $middleware): static;

}
