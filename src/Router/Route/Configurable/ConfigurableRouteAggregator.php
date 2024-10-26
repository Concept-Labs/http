<?php
namespace Concept\Http\Router\Route\Configurable;

use Concept\Config\ConfigInterface;
use RuntimeException;
use Traversable;

class ConfigurableRouteAggregator implements ConfigurableRouteAggregatorInterface
{
    private ?ConfigInterface $config = null;
    private ?ConfigurableRouteFactoryInterface $routeFactory = null;

    /**
     * Dependency injection
     * 
     * @param ConfigurableRouteFactoryInterface $routeFactory
     */
    public function __construct(ConfigurableRouteFactoryInterface $routeFactory)
    {
        $this->routeFactory = $routeFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function withConfig(ConfigInterface $config): self
    {
        $clone = clone $this;
        $clone->config = $config;

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function aggregate(): iterable
    {
        foreach ($this->getConfig() as $routeConfigData) {
            yield $this->createRoute(
                $this->getConfig()
                    ->withData($routeConfigData)
            );
        }
    }

    /**
     * Get the routes
     * 
     * @return iterable
     */
    protected function getRoutes(): iterable
    {
        return $this->getConfig()->asArray();
    }

    /**
     * Get the config
     * 
     * @return ConfigInterface
     */
    protected function getConfig(): ConfigInterface
    {
        if ($this->config === null) {
            throw new RuntimeException('Config not set');
        }
        
        return $this->config;
    }

    /**
     * Create a route
     * 
     * @param ConfigInterface $config
     * @return object
     */
    protected function createRoute(ConfigInterface $config): object
    {
        $routeFactory = $this->getRouteFactory()
            ->withConfig($config);
        
        return $routeFactory->create();
    }

    /**
     * Get the route factory
     * 
     * @return ConfigurableRouteFactoryInterface
     */
    protected function getRouteFactory(): ConfigurableRouteFactoryInterface
    {
        return $this->routeFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): Traversable
    {
        yield from $this->aggregate();
    }

}