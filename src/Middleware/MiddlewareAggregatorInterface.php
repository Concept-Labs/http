<?php
namespace Concept\Http\Middleware;

use Concept\Config\ConfigurableInterface;
use Psr\Http\Server\MiddlewareInterface;

interface MiddlewareAggregatorInterface extends ConfigurableInterface
{

    const CONFIG_NODE_MIDDLEWARE = 'middleware';
   
    /**
     * Aggregate middleware
     * 
     * @return iterable|MiddlewareInterface[]
     */
    public function aggregate(): iterable;

}
