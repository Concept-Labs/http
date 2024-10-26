<?php
namespace Concept\Http\Router;

use Concept\Http\Middleware\Configurable\ConfigurableMiddlewareInterface;

/**
 * Interface RouterInterface
 * @package Concept\Http\Router
 */
interface RouterInterface extends ConfigurableMiddlewareInterface
{
    const CONFIG_ROUTE_NODE = 'route';
}