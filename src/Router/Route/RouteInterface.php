<?php

namespace Concept\Http\Router\Route;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface RouteInterface extends RequestHandlerInterface
{
    /**
     * Check if the route matches the request
     * 
     * @param ServerRequestInterface $request
     * 
     * @return bool
     */
    public function match(ServerRequestInterface $request): bool;

}
