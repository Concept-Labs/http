<?php
namespace Concept\Http\RequestHandler;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface MiddlewareStackHandlerInterface extends RequestHandlerInterface
{

    public function withFinalHandler(RequestHandlerInterface $handler): static;

    public function addMiddleware(MiddlewareInterface $middlewares): static;

}
