<?php
namespace Concept\Http\Router\Route\Handler;

use Concept\Config\ConfigurableInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface HandlerFactoryInterface extends ConfigurableInterface
{
    /**
     * Create a handler.
     *
     * @return HandlerInterface
     */
    public function create(): RequestHandlerInterface;

}