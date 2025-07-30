<?php

namespace Concept\Http\Router\Route;

use Concept\Config\Contract\ConfigurableInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface RouteInterface extends RequestHandlerInterface, ConfigurableInterface
{
    const CONFIG_METHOD_NODE = 'method';
    const CONFIG_PATH_NODE = 'path';
    const CONFIG_HANDLER_NODE = 'handler';
    
    /**
     * Check if the route matches the request
     * 
     * @param ServerRequestInterface $request
     * 
     * @return bool
     */
    public function match(ServerRequestInterface $request): bool;

}
