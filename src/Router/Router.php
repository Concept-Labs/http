<?php

namespace Concept\Http\Router;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Concept\Singularity\Contract\Lifecycle\SharedInterface;
use Concept\Config\ConfigInterface;
use Concept\Config\Contract\ConfigurableTrait;
use Concept\Http\Router\Exception\NotFoundException;
use Concept\Http\Router\Route\RouteAggregatorInterface;
use Throwable;

class Router implements RouterInterface, SharedInterface
{
    use ConfigurableTrait;

    /**
     * Dependency injection
     * 
     * @param RouteAggregatorInterface $routeAggregator
     */
    public function __construct(private RouteAggregatorInterface $routeAggregator)
    {}


    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        foreach ($this->getRouteAggregator() as $route) {
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
    protected function getRouteAggregator(): RouteAggregatorInterface
    {
        if (!$this->routeAggregator->hasConfig()) {
            $this->routeAggregator->setConfig($this->getAggregatorConfig());
        }
        
        return $this->routeAggregator;
    }

    /**
     * Get the aggregator config
     * 
     * @return ConfigInterface
     */
    protected function getAggregatorConfig(): ConfigInterface
    {
        try {
            return $this->getConfig()->node(RouterInterface::CONFIG_ROUTE_NODE);
        } catch (Throwable) {
            throw new NotFoundException(
                'Router configuration not found. Please ensure that the configuration is set up correctly.'
            );
        }
    }    
}
