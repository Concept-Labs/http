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


    /**
     * With middleware
     *
     * @param MiddlewareInterface $middleware
     * @return static
     */
    public function withMiddleware(MiddlewareInterface $middleware): static
    {
        $clone = clone $this;
        $clone->middleware = $middleware;

        return $clone;
    }

    /**
     * With handler
     *
     * @param RequestHandlerInterface $handler
     * @return static
     */
    public function withHandler(RequestHandlerInterface $handler): static
    {
        $clone = clone $this;
        $clone->handler = $handler;

        return $clone;
    }

    /**
     * Handle the request
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws HttpRuntimeException
     */
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

    /**
     * Get the handler
     *
     * @return RequestHandlerInterface
     */
    protected function getHandler(): RequestHandlerInterface
    {
        return $this->handler;
    }

    /**
     * Get the middleware
     *
     * @return MiddlewareInterface
     * @throws HttpRuntimeException
     */
    protected function getMiddleware(): MiddlewareInterface
    {
        return $this->middleware;
    }
}
