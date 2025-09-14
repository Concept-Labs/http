<?php
namespace Concept\Http\App;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Fig\Http\Message\StatusCodeInterface;

use Concept\Http\AbstractApp;
use Concept\Http\AppInterface;
use Concept\Http\RequestHandler\MiddlewareStackHandlerInterface;
use Concept\Http\App\Config\AppConfigInterface;
use Concept\Http\App\Event\StartEvent;
use Generator;

/**
 * Class HttpApp
 * @package Concept\Http
 */

class HttpApp extends AbstractApp implements AppInterface
{
    private ?ServerRequestInterface $serverRequest = null;
    private ?ResponseInterface $response = null;

    /**
     * @var MiddlewareInterface[][]
     */
    protected array $middlewares = [];

    /**
     * Dependency injection
     * 
     * @param AppConfigInterface $config
     * @param ServerRequestFactoryInterface $serverRequestFactory
     * @param ResponseFactoryInterface $responseFactory
     * @param MiddlewareStackHandlerInterface $middlewareStackHandlerPrototype
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        private AppConfigInterface $config,
        private ServerRequestFactoryInterface $serverRequestFactory,
        private ResponseFactoryInterface $responseFactory,
        private MiddlewareStackHandlerInterface $middlewareStackHandlerPrototype,
        private EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher->dispatch(new StartEvent(
           ['app' => $this]
        ));
    }

    /**
     * Run the application
     */
    public function run(): void
    {
        $this->processMiddlewareStack();
    }

    /**
     * Get the application configuration
     *
     * @return AppConfigInterface
     */
    public function getConfig(): AppConfigInterface
    {
        return $this->config;
    }

    /**
     * Set the server request
     * 
     * @param ServerRequestInterface $request
     */
    public function withServerRequest(ServerRequestInterface $request): static
    {
        $clone = clone $this;
        $clone->serverRequest = $request;

        return $clone;
    }

    /**
     * Add middleware to the stack`
     * @param MiddlewareInterface $middleware
     */
    public function addMiddleware(MiddlewareInterface $middleware, int $priority = 100, ?string $id = null ): static 
    {
        $this->middlewares[$priority][$id ?? spl_object_id($middleware)] = $middleware;

        return $this;
    }

    /**
     * Get middleware by name
     * 
     * @param string $className
     * 
     * @return MiddlewareInterface|null
     */
    public function getMiddleware(string $className): ?MiddlewareInterface
    {
        foreach ($this->middlewares as $middlewares) {
            foreach ($middlewares as $middleware) {
                if (get_class($middleware) === $className) {
                    return $middleware;
                }
            }
        }

        return null;
    }

    /**
     * Get the middleware stack
     * 
     * @return Generator
     */
    protected function getMiddlewareStack(): Generator
    {
        ksort($this->middlewares);

        foreach ($this->middlewares as $priority => $middlewares) {
            foreach ($middlewares as $middleware) {
                yield $middleware;
            }
        }
    }

    /**
     * Process the middleware stack
     * 
     * @return static
     */
    protected function processMiddlewareStack(): static
    {
        $stackHandler = $this
            ->getMiddlewareStackHandler()
            /**
             @todo: move final handler implementation to a dedicated middleware
             */
            ->withFinalHandler(
                new class ($this->getResponseFactory()) implements RequestHandlerInterface {
                    public function __construct(private ResponseFactoryInterface $responseFactory) {}
                    public function handle(ServerRequestInterface $request): ResponseInterface {
                        return $this->responseFactory->createResponse(StatusCodeInterface::STATUS_NOT_FOUND);
                        // Alternatively, throw an exception
                        //throw new NotHandledException('No response generated.');
                    }
                }
            );
        
        foreach ($this->getMiddlewareStack() as $middleware) {
            $stackHandler = $stackHandler->addMiddleware($middleware);
        }

        $this->setResponse(
            $stackHandler->handle(
                $this->getServerRequest()
            )
        );

        return $this;
    }

    /**
     * Initialize the server request
     * 
     * @return ServerRequestInterface
     */
    protected function getServerRequest(): ServerRequestInterface
    {
        return $this->serverRequest ??= $this->getServerRequestFactory()
            ->createServerRequest(
                $_SERVER['REQUEST_METHOD'] ?? 'GET',
                $this->getServerUrl(),
                $_SERVER
            );
    }

    /**
     * Get the server URL
     @todo: improve this
     * 
     * @return string
     */
    protected function getServerUrl(): string
    {
        $host = $this->getConfig()->get('host') ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scheme = $_SERVER['REQUEST_SCHEME'] ?? 'http';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        return sprintf(
            '%s://%s%s',
            $scheme,
            $host,
            $uri
        );
    }

    /**
     * Get the server request factory
     * 
     * @return ServerRequestFactoryInterface
     */
    protected function getServerRequestFactory(): ServerRequestFactoryInterface
    {
        return $this->serverRequestFactory;
    }

    /**
     * Get the response factory
     * 
     * @return ResponseFactoryInterface
     */
    protected function getResponseFactory(): ResponseFactoryInterface
    {
        return $this->responseFactory;
    }

    /**
     * Get the response
     * 
     * @return ResponseInterface
     */
    protected function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Set the response
     * 
     * @param ResponseInterface $response
     * 
     * @return static
     */
    protected function setResponse(ResponseInterface $response): static
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get the middleware stack handler prototype
     * 
     * @return MiddlewareStackHandlerInterface
     */
    protected function getMiddlewareStackHandler(): MiddlewareStackHandlerInterface
    {
        return $this->middlewareStackHandlerPrototype;
    }

}
