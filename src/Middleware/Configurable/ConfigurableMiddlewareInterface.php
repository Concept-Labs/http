<?php

namespace Concept\Http\Middleware\Configurable;

use Psr\Http\Server\MiddlewareInterface;
use Concept\Config\ConfigurableInterface;

interface ConfigurableMiddlewareInterface extends MiddlewareInterface, ConfigurableInterface
{
   
}
