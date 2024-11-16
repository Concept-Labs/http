<?php
namespace Concept\Http\RequestHandler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewareStackHandler implements MiddlewareStackHandlerInterface
{
    /**
     * @var MiddlewareInterface[]
     */
    private array $middlewares = [];
    
    /**
     * @var RequestHandlerInterface
     */
    private RequestHandlerInterface $defaultHandler;

    /**
     * @var MiddlewareRequestHandlerInterface
     */
    private ?MiddlewareRequestHandlerInterface $middlewareRequestHandlerPrototype = null;

    /**
     * Dependency injection constructor
     */
    public function __construct(MiddlewareRequestHandlerInterface $middlewareRequestHandler)
    {
        $this->middlewareRequestHandlerPrototype = $middlewareRequestHandler;
    }

    /**
     * Set the final handler
     * 
     * @param RequestHandlerInterface $handler
     * 
     * @return self
     */
    public function withFinalHandler(RequestHandlerInterface $handler): self
    {
        $clone = clone $this;
    
        
        return $clone;
    }

    /**
     * Add a middleware to the stack
     * 
     * @param MiddlewareInterface $middlewares
     * 
     * @return self
     */
    public function addMiddleware(MiddlewareInterface $middlewares): self
    {
        $this->middlewares[] = $middlewares;

        return $this;
    }

    /**
     * Get the middleware collection
     * 
     * @return MiddlewareInterface[]
     */
    protected function getMiddleWareCollection(): array
    {
        return $this->middlewares;
    }

    /**
     * Handle the request
     * 
     * @param ServerRequestInterface $request
     * 
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $handler = $this->getFinalHandler();

        foreach (array_reverse($this->middlewares) as $middleware) {
            $handler = $this->getMiddelwareRequestHandlerPrototype()
                ->withMiddleware($middleware)
                ->withHandler($handler);
        }

        return $handler->handle($request);
    }



    /**
     * Get the middleware request handler prototype
     * 
     * @return MiddlewareRequestHandlerInterface
     */
    protected function getMiddelwareRequestHandlerPrototype(): MiddlewareRequestHandlerInterface
    {
        return clone $this->middlewareRequestHandlerPrototype;
    }

    /**
     * Get the final handler
     * 
     * @return RequestHandlerInterface
     */
    protected function getFinalHandler(): RequestHandlerInterface
    {
        return $this->defaultHandler ?? new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new \RuntimeException('No final handler configured');
            }
        };
    }
}
