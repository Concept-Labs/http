<?php
namespace Concept\Http\Router\Route\Handler;

use Concept\Config\Config;
use Concept\Config\ConfigInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Concept\Config\Contract\ConfigurableInterface;
use Concept\Config\Contract\ConfigurableTrait as CTrait;
use Concept\Http\Router\Route\Handler\Middleware\MiddlewareFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;

class RouteHandler implements RouteHandlerInterface
{

     use CTrait {
        setConfig as private traitSetConfig;
    }

    /** @var array<int, list<MiddlewareInterface>> */
    private array $middlewares = [];

    
    private ?RequestHandlerInterface $compiledStack = null;

    
    private bool $routeMiddlewaresAggregated = false;


    /**
     * The request handler instance
     * @var RequestHandlerInterface|null
     */
    protected ?RequestHandlerInterface $requestHandler = null;

    public function __construct(
        private HandlerFactoryInterface $handlerFactory,
        private ResponseFactoryInterface $responseFactory,
        private MiddlewareFactory $middlewareFactory
    ) {}

    /**
     * Reset and set new config
     * 
     * {@inheritDoc}
     */
    public function setConfig(ConfigInterface $config): static
    {
        $this->compiledStack = null;
        $this->routeMiddlewaresAggregated = false;
        $this->middlewares = [];

        return $this->traitSetConfig($config);
    }

    
    public function withMiddleware(MiddlewareInterface $middleware, int $priority = 0): static
    {
        $this->compiledStack = null; // інвалідовуємо кеш
        $this->middlewares[$priority] ??= [];
        $this->middlewares[$priority][] = $middleware;
        return $this;
    }

    
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->aggregateRouteMiddlewaresFromConfigOnce();
        return $this->processMiddlewareStack($request);
    }

    /**
     * 
     *
     * 
     * {
     *   "preference": "\\App\\Handler",
     *   "layout": { ... },
     *   "middleware": {
     *     "id": {
     *       "preference": "\\Vendor\\Middleware\\Class",
     *       "priority": 100,           // optional
     *       "config": { ... }          // optional
     *     }
     *   }
     * }
     */
    protected function aggregateRouteMiddlewaresFromConfigOnce(): void
    {
        if ($this->routeMiddlewaresAggregated) {
            return;
        }
        $cfg = $this->getConfig();
        if (!$cfg) {
            $this->routeMiddlewaresAggregated = true;
            return;
        }

        $mwNode = $cfg->get('middleware') ?? [];
        if (is_array($mwNode)) {
            foreach ($mwNode as $id => $def) {
                if (!is_array($def)) {
                    continue;
                }

                $preference    = $def['preference'] ?? throw new \RuntimeException(
                    sprintf('Middleware "%s" preference not set', $id)
                );

                $priority = (int)($def['priority'] ?? 0);
                $mConf    = $def['config'] ?? [];

                try {
                    $mw = $this->createMiddleware($preference, $mConf);
                } catch (\Throwable $e) {
                    throw new \RuntimeException(
                        sprintf('Error creating middleware "%s": %s', $id, $e->getMessage()),
                        0,
                        $e
                    );
                }

                $this->withMiddleware(
                    $mw,
                    $priority
                );
            }
        }

        $this->routeMiddlewaresAggregated = true;
    }

    
    protected function createMiddleware(string $preference, array $config): MiddlewareInterface
    {
        /** @var MiddlewareInterface $mw */
        $mw = $this->getMiddlewareFactory()->create([
            'preference' => $preference,
            'config'     => $config,
        ]);

        return $mw;
    }

    protected function getMiddlewareFactory(): MiddlewareFactory
    {
        return $this->middlewareFactory;
    }

    
    protected function processMiddlewareStack(ServerRequestInterface $request): ResponseInterface
    {
        
        if (empty($this->middlewares)) {
            return $this->getHandler()->handle($request);
        }

        
        if ($this->compiledStack instanceof RequestHandlerInterface) {
            return $this->compiledStack->handle($request);
        }

        
        krsort($this->middlewares, SORT_NUMERIC);

        
        $ordered = [];
        foreach ($this->middlewares as $priority => $list) {
            foreach ($list as $mw) {
                if (!$mw instanceof MiddlewareInterface) {
                    throw new \RuntimeException(sprintf(
                        'Route middleware at priority %s must implement %s',
                        (string)$priority, MiddlewareInterface::class
                    ));
                }
                $ordered[] = $mw;
            }
        }

        
        $next = $this->getHandler();

        
        for ($i = count($ordered) - 1; $i >= 0; $i--) {
            $mw = $ordered[$i];
            $next = new class($mw, $next) implements RequestHandlerInterface {
                public function __construct(
                    private MiddlewareInterface $mw,
                    private RequestHandlerInterface $next
                ) {}
                public function handle(ServerRequestInterface $request): ResponseInterface
                {
                    return $this->mw->process($request, $this->next);
                }
            };
        }

        
        $this->compiledStack = $next;

        return $this->compiledStack->handle($request);
    }

    protected function getHandler(): RequestHandlerInterface
    {
        if ($this->requestHandler === null) {
            $factory = $this->getHandlerFactory();

            if ($factory instanceof ConfigurableInterface) {
                $factory->setConfig($this->getConfig());
            }

            $this->requestHandler = $factory->create()
                ->withResponseFactory($this->responseFactory);

            if ($this->requestHandler instanceof ConfigurableInterface) {
                $this->requestHandler->setConfig($this->getConfig());
            }
        }
        return $this->requestHandler;
    }

    protected function getHandlerFactory(): HandlerFactoryInterface
    {
        return $this->handlerFactory;
    }
}
