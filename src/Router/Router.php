<?php

namespace Concept\Http\Router;

use Concept\Config\ConfigInterface;
use Concept\Config\Traits\ConfigurableTrait;
use Concept\Http\Router\Route\RouteAggregatorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Router implements RouterInterface
{
    use ConfigurableTrait;

    private ?RouteAggregatorInterface $routeAggregatorPrototype = null;

    

    /**
     * Dependency injection
     * 
     * @param RouteAggregatorInterface $routeAggregator
     */
    public function __construct(RouteAggregatorInterface $routeAggregator)
    {
        $this->routeAggregatorPrototype = $routeAggregator;
    }


    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        
        $routeAggregator = $this->getRouteAggregatorPrototype()
            ->withConfig($this->getAggregatorConfig());

        foreach ($routeAggregator as $route) {
            if ($route->match($request)) {
                return $route->handle($request);
            }
        }

        /**
         * Continue to the next middleware
         */
        return $handler->handle($request);
    }

    /**
     * {@inheritDoc}
     */
    protected function getRouteAggregatorPrototype(): RouteAggregatorInterface
    {
        return clone $this->routeAggregatorPrototype;
    }

    /**
     * Get the aggregator config
     * 
     * @return ConfigInterface
     */
    protected function getAggregatorConfig(): ConfigInterface
    {
        return $this->getConfig()->fromPath(RouterInterface::CONFIG_ROUTE_NODE);
    }    
}
