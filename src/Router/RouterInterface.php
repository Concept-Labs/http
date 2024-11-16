<?php
namespace Concept\Http\Router;

use Concept\Http\Middleware\MiddlewareInterface;

/**
 * Interface RouterInterface
 * @package Concept\Http\Router
 */
interface RouterInterface extends MiddlewareInterface
{
    const CONFIG_ROUTE_NODE = 'route';
}