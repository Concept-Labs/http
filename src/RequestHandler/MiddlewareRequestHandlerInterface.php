<?php
namespace Concept\Http\RequestHandler;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface MiddlewareRequestHandlerInterface extends RequestHandlerInterface
{
    public function withMiddleware(MiddlewareInterface $middleware): static;

    public function withHandler(RequestHandlerInterface $handler): static;
}