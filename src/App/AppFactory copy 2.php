<?php
namespace Concept\Http\App;

use Concept\App\AppFactoryInterface;
use Concept\App\AppInterface;
use Concept\App\Exception\RuntimeException;
use Concept\Config\ConfigInterface;
use Concept\Config\Traits\ConfigurableTrait;
use Concept\Http\Middleware\MiddlewareAggregatorInterface;
use Concept\Singularity\Factory\FactoryInterface;

class AppFactory implements AppFactoryInterface
{

    use ConfigurableTrait;

    private ?FactoryInterface $factory = null;
    private ?MiddlewareAggregatorInterface $middlewareAggregator = null;
    private ?AppInterface $app = null;
    
    /**
     * Dependency injection
     * 
     * @param FactoryInterface $factory
     * @param MiddlewareAggregatorInterface $middlewareAggregator
     */
    public function __construct(
        FactoryInterface $factory,
        MiddlewareAggregatorInterface $middlewareAggregator
    )
    {
        $this->factory = $factory;
        $this->middlewareAggregator = $middlewareAggregator;
    }


    /**
     * {@inheritDoc}
     */
    public function create(): AppInterface
    {
        return $this
            ->createAppInstance()
            ->middlewareAggregate()
            ->getAppInstance();
    }

    /**
     * Create app instance
     * 
     * @return static
     */
    protected function createAppInstance(): static
    {
        $this->app = $this
            ->getFactory()
            ->create(AppInterface::class)
            ->withConfig($this->getConfig());

        return $this;
    }

    /**
     * Aggregate middleware
     * 
     * @return static
     */
    protected function middlewareAggregate(): static
    {
        $middlewareAggregator = $this->getMiddlewareAggregator()
            ->withConfig($this->getMiddlewareConfig());

        foreach ($middlewareAggregator as $middleware) {
            $this
                ->getAppInstance()
                ->addMiddleware($middleware);
        }

        return $this;
    }

    /**
     * Get the factory
     * 
     * @return FactoryInterface
     */
    protected function getFactory(): FactoryInterface
    {
        return $this->factory;
    }

    /**
     * Get the middleware aggregator
     * 
     * @return MiddlewareAggregatorInterface
     */
    protected function getMiddlewareAggregator(): MiddlewareAggregatorInterface
    {
        return $this->middlewareAggregator;
    }

    /**
     * Get the middleware config
     * 
     * @return ConfigInterface
     */
    protected function getMiddlewareConfig(): ConfigInterface
    {
        if (!$this->getConfig()->has(MiddlewareAggregatorInterface::CONFIG_NODE_MIDDLEWARE)) {
            throw new RuntimeException(
                sprintf(
                    'No middleware configured: "%s" node not found',
                    MiddlewareAggregatorInterface::CONFIG_NODE_MIDDLEWARE
                )
            );
        }

        return $this->getConfig()
            ->from(MiddlewareAggregatorInterface::CONFIG_NODE_MIDDLEWARE);
    }

    /**
     * Get the app
     * 
     * @return AppInterface
     */
    protected function getAppInstance(): AppInterface
    {
        if (null === $this->app) {
            throw new RuntimeException('App not created');
        }

        return $this->app;
    }
}
