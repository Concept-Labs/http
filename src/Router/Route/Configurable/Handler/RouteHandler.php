<?php
namespace Concept\Http\Router\Route\Configurable\Handler;

use Concept\Config\Traits\ConfigurableTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteHandler implements RouteHandlerInterface
{
    use ConfigurableTrait;

    protected ?HandlerFactoryInterface $factory = null;
    protected ?RequestHandlerInterface $requestHandler = null;

    /**
     * Dependency injection
     * 
     * @param FactoryInterface $factory
     */
    public function __construct(HandlerFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this
            ->getHandler()
            ->handle($request);   
    }

    /**
     * Get the request handler
     * 
     * @return RequestHandlerInterface
     */
    protected function getHandler(): RequestHandlerInterface
    {
        if ($this->requestHandler === null) {
            $this->requestHandler = $this
                ->getFactory()
                ->withConfig($this->getConfig())
                ->create();
        }

        return $this->requestHandler;
    }

    /**
     * Get the factory
     * 
     * @return HandlerFactoryInterface
     */
    protected function getFactory(): HandlerFactoryInterface
    {
        return $this->factory;
    }
}