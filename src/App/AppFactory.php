<?php
namespace Concept\Http\App;

use Concept\Http\AppInterface;
use Concept\Http\AppFactoryInterface;
use Concept\Http\App\Config\AppConfigInterface;
use Concept\Http\Middleware\MiddlewareAggregatorInterface;
use Concept\Singularity\Factory\ServiceFactory;
use Concept\Singularity\Contract\Initialization\InjectableInterface;
use Concept\Singularity\Plugin\Attribute\Injector;

class AppFactory extends ServiceFactory implements AppFactoryInterface, InjectableInterface
{

    //use ConfigurableTrait;

    private ?AppInterface $app = null;
    private ?AppConfigInterface $config = null;
    private ?MiddlewareAggregatorInterface $middlewareAggregator = null;

    /**
     * Dependency injection
     * 
     * @param AppConfigInterface $config
     * @param MiddlewareAggregatorInterface $middlewareAggregator
     * 
     * @return void
     */
    #[Injector]
    public function depends(
        AppConfigInterface $config, 
        MiddlewareAggregatorInterface $middlewareAggregator
    ): void
    {
        $this->config = $config;
        $this->middlewareAggregator = $middlewareAggregator;
    }

     /**
     * {@inheritDoc}
     */
    public function create(array $args = []): AppInterface
    {
        // Create the app instance
        return $this
            ->createAppInstance($args) // Step 1: Create app instance
            ->aggregateMiddleware()    // Step 2: Aggregate middleware stack
            ->getAppInstance();        // Step 3: Return app instance
    }

    /**
     * Get the app config
     * 
     * @return AppConfigInterface
     */
    protected function getConfig(): AppConfigInterface
    {
        return $this->config;
    }

    /**
     * Get the app
     * 
     * @return AppInterface
     */
    protected function getAppInstance(): AppInterface
    {
        if (null === $this->app) {
            throw new \RuntimeException('App instance has not been created. Call createAppInstance() first.');
        }

        return $this->app;
    }

    /**
     * Create app instance
     * 
     * @return static
     */
    protected function createAppInstance(array $args = []): static
    {
        $this->app = $this
            ->createService(AppInterface::class, $args);

        return $this;
    }

    /**
     * Aggregate middleware
     * 
     * @return static
     */
    protected function aggregateMiddleware(): static
    {
        foreach ($this->getMiddlewareAggregator() as $middleware) {
            $this
                ->getAppInstance()
                ->addMiddleware($middleware);
        }

        return $this;
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

    
}
