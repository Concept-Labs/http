<?php
namespace Concept\Http\App;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Concept\Http\AppFactoryInterface;
use Concept\Http\AppInterface;
use Concept\Config\ConfigInterface;
use Concept\Config\Contract\ConfigurableTrait;
use Concept\Http\Middleware\MiddlewareAggregatorInterface;
use Concept\Singularity\Factory\FactoryInterface;
use Concept\Singularity\Factory\ServiceFactory;
use Concept\Singularity\Context\ProtoContextInterface;

class AppFactory extends ServiceFactory implements AppFactoryInterface
{

    use ConfigurableTrait;

    protected ?AppInterface $app = null;
    
    /**
     * Dependency injection
     * 
     * @param FactoryInterface $factory
     * @param MiddlewareAggregatorInterface $middlewareAggregator
     */
    public function __construct(
        ContainerInterface $container,
        ProtoContextInterface $context,
        private MiddlewareAggregatorInterface $middlewareAggregator,
        private EventDispatcherInterface $eventDispatcher
    )
    {
        parent::__construct($container, $context);
    }


    /**
     * {@inheritDoc}
     */
    public function create(array $args = []): AppInterface
    {
        return $this
            ->createAppInstance($args)
            //->
            ->middlewareAggregate()
            ->getAppInstance();
    }

    /**
     * Create app instance
     * 
     * @return static
     */
    protected function createAppInstance(array $args = []): static
    {
        $this->app = $this
            ->createService(AppInterface::class, $args)
            ->setConfig($this->getConfig());

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
            ->setConfig($this->getMiddlewareConfig());

        foreach ($middlewareAggregator as $middleware) {
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

    /**
     * Get the middleware config
     * 
     * @return ConfigInterface
     */
    protected function getMiddlewareConfig(): ConfigInterface
    {
        if (!$this->getConfig()->has(MiddlewareAggregatorInterface::CONFIG_NODE_MIDDLEWARE)) {
            throw new \RuntimeException(
                sprintf(
                    'No middleware configured: "%s" node not found',
                    MiddlewareAggregatorInterface::CONFIG_NODE_MIDDLEWARE
                )
            );
        }

        return $this->getConfig()
            ->node(MiddlewareAggregatorInterface::CONFIG_NODE_MIDDLEWARE);
    }

    /**
     * Get the app
     * 
     * @return AppInterface
     */
    protected function getAppInstance(): AppInterface
    {
        if (null === $this->app) {
            throw new \RuntimeException('App not created');
        }

        return $this->app;
    }
}
