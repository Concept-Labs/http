<?php
namespace Concept\Http\App;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Concept\Config\ConfigInterface;
use Concept\Factory\FactoryInterface;
use Concept\Http\Router\RouterInterface;
use Concept\Http\Response\FlusherInterface;

/**
 * Class HttpApp
 * @package Concept\Http\App
 */
class HttpApp implements HttpAppInterface
{
    protected ?ConfigInterface $config = null;
    protected ?ContainerInterface $container = null;
    protected ?FactoryInterface $factory = null;
    protected ?ServerRequestFactoryInterface $serverRequestFactory = null;
    protected ?ServerRequestInterface $serverRequest = null;
    protected ?RouterInterface $router = null;
    protected ?ResponseInterface $response = null;
    protected ?FlusherInterface $flusher = null;
    protected array $middlewares = [];

    public function __construct(
        ServerRequestFactoryInterface $serverRequestFactory,
        RouterInterface $router,
        FlusherInterface $flusher
    ) {
        $this->serverRequestFactory = $serverRequestFactory;
        $this->router = $router;
        $this->flusher = $flusher;
    }

    /**
     * Set the server request
     * @param ServerRequestInterface $serverRequest
     */
    public function withServerRequest(ServerRequestInterface $serverRequest): self
    {
        $clone = clone $this;
        $clone->serverRequest = $serverRequest;

        return $clone;
    }

    /**
     * Set the configuration
     * @param ConfigInterface $config
     */
    public function withConfig(ConfigInterface $config): self
    {
        $clone = clone $this;
        $clone->config = $config;

        return $clone;
    }

    /**
     * Set the container
     * @param ContainerInterface $container
     */
    public function withContainer(ContainerInterface $container): self
    {
        $clone = clone $this;
        $clone->container = $container;

        return $clone;
    }

    /**
     * Set the factory
     * @param FactoryInterface $factory
     */
    public function withFactory(FactoryInterface $factory): self
    {
        $clone = clone $this;
        $clone->factory = $factory;

        return $clone;
    }

    /**
     * Add middleware to the stack
     * @param MiddlewareInterface $middleware
     */
    public function withAddedMiddleware(MiddlewareInterface $middleware): self 
    {
        $clone = clone $this;
        $clone->middlewares[] = $middleware;

        return $clone;
    }

    /**
     * Run the application
     */
    public function run(): void
    {
        $this->processMiddlewareStack()
            ->flush();
    }

    /**
     * Initialize the server request
     * 
     * @return ServerRequestInterface
     */
    protected function getServerRequest(): ServerRequestInterface
    {
        if ($this->serverRequest === null) {
            $this->serverRequest = $this->getServerRequestFactory()
                ->createServerRequest(
                    $_SERVER['REQUEST_METHOD'] ?? 'GET',
                    $_SERVER['REQUEST_URI'] ?? '/',
                    $_SERVER
                );
        }

        return $this->serverRequest;
    }

    /**
     * Create a middleware handler to process the middleware stack
     * @return self
     */
    protected function processMiddlewareStack(): self
    {
        $handler = $this->getRouterHandler();

        foreach (array_reverse($this->middlewares) as $middleware) {
            
            if (!($middleware instanceof MiddlewareInterface)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        "Middleware must implement %s",
                        MiddlewareInterface::class
                    )
                );
            }

            $handler = new class($middleware, $handler) implements RequestHandlerInterface {
                private MiddlewareInterface $middleware;
                private RequestHandlerInterface $handler;

                public function __construct(MiddlewareInterface $middleware, RequestHandlerInterface $handler)
                {
                    $this->middleware = $middleware;
                    $this->handler = $handler;
                }

                public function handle(ServerRequestInterface $request): ResponseInterface
                {
                    return $this->middleware->process($request, $this->handler);
                }
            };
        }

        $this->response = $handler->handle($this->getServerRequest());

        return $this;
    }

    /**
     * Get the router handler
     * @return RequestHandlerInterface
     */
    protected function getRouterHandler(): RequestHandlerInterface
    {
        return new class($this->getRouter()) implements RequestHandlerInterface {
            private RouterInterface $router;

            public function __construct(RouterInterface $router)
            {
                $this->router = $router;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->router->dispatch($request);
            }
        };
    }

    /**
     * Get the router
     * @return RouterInterface
     */
    protected function getRouter(): RouterInterface
    {
        // Clone the router to avoid modifying the original
        $router = clone $this->router;

        foreach ($this->getRoutes() as $path => $route) {
            $router->addRoute(
                $route['method'], 
                $path, 
                $this->getFactory()
                    ->withContainer($this->getContainer())
                    ->withServiceId($route['handler'])
                    ->create()
            );
        }

        return $router;
    }

    /**
     * Get the routes
     * @return array
     */
    protected function getRoutes(): array
    {
        return $this->getConfig()->get($this->getHandlerNodesPath());
    }

    /**
     * Get the handler nodes path
     * @return string
     */
    protected function getHandlerNodesPath(): string
    {
        return sprintf(
            "%s.%s.%s",
            self::CONFIG_NODE,
            RouterInterface::CONFIG_NODE,
            RouterInterface::CONFIG_NODE_HANDLER
        );
    }

    /**
     * Flush the response
     * @return self
     */
    protected function flush(): self
    {
        $this->getFlusher()->flush($this->getResponse());
        return $this;
    }

    /**
     * Get the configuration
     * @return ConfigInterface
     */
    protected function getConfig(): ConfigInterface
    {
        return $this->config;
    }

    /**
     * Get the container
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Get the factory
     * @return FactoryInterface
     */
    protected function getFactory(): FactoryInterface
    {
        return $this->factory;
    }

    /**
     * Get the server request factory
     * @return ServerRequestFactoryInterface
     */
    protected function getServerRequestFactory(): ServerRequestFactoryInterface
    {
        return $this->serverRequestFactory;
    }

    /**
     * Get the response
     * @return ResponseInterface
     */
    protected function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Get the flusher
     * @return FlusherInterface
     */
    protected function getFlusher(): FlusherInterface
    {
        return $this->flusher;
    }
}
