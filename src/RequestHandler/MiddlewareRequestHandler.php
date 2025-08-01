<?php
namespace Concept\Http\RequestHandler;

use Concept\Http\Exception\HttpRuntimeException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewareRequestHandler implements MiddlewareRequestHandlerInterface
{

    private ?MiddlewareInterface $middleware = null;
    private ?RequestHandlerInterface $handler = null;


    public function withMiddleware(MiddlewareInterface $middleware): static
    {
        $clone = clone $this;
        $clone->middleware = $middleware;

        return $clone;
    }

    public function withHandler(RequestHandlerInterface $handler): static
    {
        $clone = clone $this;
        $clone->handler = $handler;

        return $clone;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->getMiddleware() === null) {
            throw new HttpRuntimeException('No middleware configured');
        }

        if ($this->getHandler() === null) {
            throw new HttpRuntimeException('No handler configured');
        }

        return $this
            ->getMiddleware()
                ->process(
                    $request,
                    $this->getHandler()
                );
    }

    protected function getHandler(): RequestHandlerInterface
    {
        return $this->handler;
    }

    protected function getMiddleware(): MiddlewareInterface
    {
        return $this->middleware;
    }
}
