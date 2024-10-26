<?php

namespace Concept\Http\Router\Route\Configurable;


use Concept\Config\ConfigurableInterface;
use Concept\Http\Router\Route\RouteInterface;

interface ConfigurableRouteInterface extends RouteInterface, ConfigurableInterface
{
    const CONFIG_METHOD_NODE = 'method';
    const CONFIG_PATH_NODE = 'path';
    const CONFIG_HANDLER_NODE = 'handler';
}
