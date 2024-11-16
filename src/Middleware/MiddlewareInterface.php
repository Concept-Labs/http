<?php

namespace Concept\Http\Middleware;

use Psr\Http\Server\MiddlewareInterface as PsrMiddlewareInterface;
use Concept\Config\ConfigurableInterface;

interface MiddlewareInterface extends PsrMiddlewareInterface, ConfigurableInterface
{
   
}
