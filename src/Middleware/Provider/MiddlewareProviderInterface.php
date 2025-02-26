<?php
namespace Concept\Http\Middleware\Provider;

use Concept\Http\Middleware\MiddlewareInterface;

interface MiddlewareProviderInterface
{
    
    public function addMiddleware(MiddlewareInterface $middleware, int $priority = 0): static;

    /**
     * Get the middleware list
     * 
     * @return array
     */
    public function getMiddlewareList(): array;

}