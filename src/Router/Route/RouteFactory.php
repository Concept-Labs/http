<?php
namespace Concept\Http\Router\Route;

use Concept\Factory\AbstractFactory;

class RouteFactory extends AbstractFactory
{
    
    /**
     * {@inheritDoc}
     */
    public function create(): object
    {
        $route = $this->withServiceId(RouteInterface::class)
            ->create();

        return $route;
    }
}