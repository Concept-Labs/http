<?php
namespace Concept\Http\Router\Route\Handler;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Concept\Config\Contract\ConfigurableInterface;
use Concept\Config\Contract\ConfigurableTrait;
use Psr\Http\Message\ResponseFactoryInterface;

class RouteHandler implements RouteHandlerInterface
{
    use ConfigurableTrait;

    /**
     * The request handler instance
     * 
     * @var RequestHandlerInterface|null
     */
    protected ?RequestHandlerInterface $requestHandler = null;

    /**
     * Dependency injection
     * 
     * @param HandlerFactoryInterface $handlerFactory
     */
    public function __construct(
        private HandlerFactoryInterface $handlerFactory,
        private ResponseFactoryInterface $responseFactory
        )
    {}

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->getHandler()
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
            $factory = $this
                ->getHandlerFactory();
            if ($factory instanceof ConfigurableInterface) {
                //pass the configuration to the factory
                $factory->setConfig($this->getConfig());
            }

            $this->requestHandler = $factory->create()
                ->withResponseFactory($this->responseFactory);

            if ($this->requestHandler instanceof ConfigurableInterface) {
                //pass the configuration to the handler
                $this->requestHandler->setConfig($this->getConfig());
            }
        }

        return $this->requestHandler;
    }

    /**
     * Get the Handler factory
     * 
     * @return HandlerFactoryInterface
     */
    protected function getHandlerFactory(): HandlerFactoryInterface
    {
        return $this->handlerFactory;
    }
}