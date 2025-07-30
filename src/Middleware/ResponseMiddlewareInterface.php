<?php
namespace Concept\Http\Middleware;

use Concept\Config\Contract\ConfigurableInterface;
//use Psr\Http\Server\MiddlewareInterface;

interface ResponseMiddlewareInterface extends MiddlewareInterface, ConfigurableInterface
{

}