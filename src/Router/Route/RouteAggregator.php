<?php
namespace Concept\Http\Router\Route;

use Concept\Config\Config;
use Concept\Config\ConfigInterface;
use Concept\Config\Contract\ConfigurableTrait;

use Concept\Http\Router\Route\RouteInterface;
use Traversable;

/**
 * Route aggregator
 * 
 * Aggregates routes from configuration and creates route instances.
 * Uses prototype pattern for efficient route creation.
 */
class RouteAggregator implements RouteAggregatorInterface
{
 
    use ConfigurableTrait;

    private ?RouteInterface $routePrototype = null;

    public function __construct(RouteInterface $route)
    {
        $this->routePrototype = $route;
    }

    /**
     * {@inheritDoc}
     */
    public function createRoute(ConfigInterface $config): RouteInterface
    {
        $route = $this->getRoutePrototype()
            ->setConfig($config);
        
        return $route;
    }

    /**
     * Get the route prototype
     * 
     * @return RouteInterface
     */
    protected function getRoutePrototype(): RouteInterface
    {
        return clone $this->routePrototype;
    }

    /**
     * Aggregate the routes from configuration
     * 
     * Iterates through route configuration and creates route instances.
     * Each route is configured with its path from the configuration key.
     * 
     * @return Traversable<RouteInterface>
     */
    protected function aggregate(): Traversable
    {
        foreach ($this->getConfig() as $path => $routeConfigData) {
            yield $this->createRoute(
                Config::fromArray($routeConfigData + ['path' => $path])
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): Traversable
    {
        yield from $this->aggregate();
    }

}