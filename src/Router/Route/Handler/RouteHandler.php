<?php
namespace Concept\Http\Router\Route\Handler;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Concept\Config\Contract\ConfigurableInterface;
use Concept\Config\Contract\ConfigurableTrait;

class RouteHandler implements RouteHandlerInterface
{
    use ConfigurableTrait;

    protected ?RequestHandlerInterface $requestHandler = null;

    /**
     * Dependency injection
     * 
     * @param HandlerFactoryInterface $factory
     */
    public function __construct(protected HandlerFactoryInterface $factory)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createHandler()
            ->handle($request);
    }

    /**
     * Get the request handler
     * 
     * @return RequestHandlerInterface
     */
    protected function createHandler(): RequestHandlerInterface
    {
        if ($this->requestHandler === null) {
            $factory = $this
                ->getHandlerFactory();
            if ($factory instanceof ConfigurableInterface) {
                //pass the configuration to the factory
                $factory->setConfig($this->getConfig());
            }

            $this->requestHandler = $factory->create();

            if ($this->requestHandler instanceof ConfigurableInterface) {
                //pass the configuration to the handler
                $this->requestHandler->setConfig($this->getConfig());
            }
        }

        return $this->requestHandler;
    }

    /**
     * Get the factory
     * 
     * @return HandlerFactoryInterface
     */
    protected function getHandlerFactory(): HandlerFactoryInterface
    {
        return $this->factory;
    }
}