<?php
namespace Concept\Http\Router\Route;

use Concept\Config\ConfigInterface;
use Concept\Config\Traits\ConfigurableTrait;

use Concept\Http\Router\Route\RouteInterface;
use Traversable;

class RouteAggregator implements RouteAggregatorInterface
{
 
    use ConfigurableTrait;

    // private ?ConfigurableRouteFactoryInterface $routeFactory = null;

    // /**
    //  * Dependency injection
    //  * 
    //  * @param ConfigurableRouteFactoryInterface $routeFactory
    //  */
    // public function __construct(ConfigurableRouteFactoryInterface $routeFactory)
    // {
    //     $this->routeFactory = $routeFactory;
    // }

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
            ->withConfig($config);
        
        return $route;
    }

    protected function getRoutePrototype(): RouteInterface
    {
        return clone $this->routePrototype;
    }

    /**
     * Aggregate the routes
     * 
     * @return Traverseable
     */
    protected function aggregate(): Traversable
    {
        foreach ($this->getConfig() as $routeConfigData) {
            yield $this->createRoute(
                $this->getConfig()
                    ->withData($routeConfigData)
            );
        }
    }

    /**
     * Create a route
     * 
     * @param ConfigInterface $config
     * @return object
     */
    // protected function createRoute(ConfigInterface $config): object
    // {
    //     $routeFactory = $this->getRouteFactory()
    //         ->withConfig($config);
        
    //     return $routeFactory->create();
    // }

    /**
     * Get the route factory
     * 
     * @return ConfigurableRouteFactoryInterface
     */
    // protected function getRouteFactory(): ConfigurableRouteFactoryInterface
    // {
    //     return $this->routeFactory;
    // }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): Traversable
    {
        yield from $this->aggregate();
    }

}