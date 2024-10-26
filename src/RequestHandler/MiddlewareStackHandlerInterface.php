<?php
namespace Concept\Http\RequestHandler;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface MiddlewareStackHandlerInterface extends RequestHandlerInterface
{

    public function withFinalHandler(RequestHandlerInterface $handler): self;

    public function addMiddleware(MiddlewareInterface $middlewares): self;

}
