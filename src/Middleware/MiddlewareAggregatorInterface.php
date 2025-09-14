<?php
namespace Concept\Http\Middleware;

use Concept\Config\Contract\ConfigurableInterface;
use IteratorAggregate;

interface MiddlewareAggregatorInterface extends IteratorAggregate
{

    const CONFIG_NODE_MIDDLEWARE = 'middleware';

}
