<?php
namespace Concept\Http\Middleware;

use Concept\Config\ConfigurableInterface;
use Psr\Http\Server\MiddlewareInterface;

interface ResponseMiddlewareInterface extends MiddlewareInterface, ConfigurableInterface
{
    
}