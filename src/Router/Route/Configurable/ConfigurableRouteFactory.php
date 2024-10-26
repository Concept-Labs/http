<?php
namespace Concept\Http\Router\Route\Configurable;

use Concept\Config\Traits\ConfigurableTrait;
use Concept\Http\Router\Route\RouteFactory;
use Concept\Http\Router\Route\RouteInterface;

class ConfigurableRouteFactory extends RouteFactory implements ConfigurableRouteFactoryInterface
{
    use ConfigurableTrait;

    private ?ConfigurableRouteInterface $routePrototype = null;

    public function __construct(ConfigurableRouteInterface $route)
    {
        $this->routePrototype = $route;
    }

    /**
     * {@inheritDoc}
     */
    public function create(): RouteInterface
    {
        $route = $this->getRoutePrototype()
            ->withConfig($this->getConfig());
        
        return $route;
    }

    protected function getRoutePrototype(): ConfigurableRouteInterface
    {
        return clone $this->routePrototype;
    }

}