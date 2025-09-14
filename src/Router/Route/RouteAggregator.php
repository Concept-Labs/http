<?php
namespace Concept\Http\Router\Route;

use Concept\Config\Config;
use Concept\Config\ConfigInterface;
use Concept\Config\Contract\ConfigurableTrait;

use Concept\Http\Router\Route\RouteInterface;
use Traversable;


/**
 @todo: refactor the aggregator, use static Route::match()
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
     * Aggregate the routes
     * 
     * @return Traverseable
     */
    protected function aggregate(): Traversable
    {
        foreach ($this->getConfig() as $path => $routeConfigData) {
            //$node = $this->getConfig()->node($path);//->set('path', $path);
            yield $this->createRoute(
                //$node
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