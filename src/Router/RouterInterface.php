<?php
namespace Concept\Http\Router;

use Concept\Config\Contract\ConfigurableInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Interface RouterInterface
 * @package Concept\Http\Router
 */
interface RouterInterface extends MiddlewareInterface, ConfigurableInterface
{
    const CONFIG_ROUTE_NODE = 'route';
}