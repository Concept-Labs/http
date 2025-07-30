<?php

namespace Concept\Http\Middleware;

use Psr\Http\Server\MiddlewareInterface as PsrMiddlewareInterface;
use Concept\Config\Contract\ConfigurableInterface;

interface MiddlewareWrapperInterface extends PsrMiddlewareInterface, ConfigurableInterface
{
   
}
