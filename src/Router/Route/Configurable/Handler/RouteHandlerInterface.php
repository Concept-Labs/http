<?php
namespace Concept\Http\Router\Route\Configurable\Handler;

use Concept\Config\ConfigurableInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface RouteHandlerInterface extends RequestHandlerInterface, ConfigurableInterface
{
}