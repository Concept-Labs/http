<?php
namespace Concept\Http\Middleware;

//use Psr\Http\Server\MiddlewareInterface;

use Concept\Config\Config;
use Concept\Config\Contract\ConfigurableInterface;
use Concept\Http\App\Config\AppConfigInterface;
use Traversable;

class MiddlewareAggregator implements MiddlewareAggregatorInterface
{
    /**
     * Dependency injection
     * 
     * @param MiddlewareWrapperInterface $middleware
     */
    public function __construct(
        private MiddlewareWrapperInterface $middlewareWrapperPrototype,
        private AppConfigInterface $appConfig
    )
    {}

    /**
     * Get the app config
     * 
     * @return AppConfigInterface
     */
    protected function getConfig(): AppConfigInterface
    {
        return $this->appConfig;
    }

    /**
     * Get the app config
     * 
     * @return AppConfigInterface
     */
    protected function getMiddlewareConfig(): iterable
    {
        if (!$this->appConfig->has(MiddlewareAggregatorInterface::CONFIG_NODE_MIDDLEWARE)) {
            throw new \RuntimeException(
                sprintf(
                    'No middleware configured: "%s" node not found',
                    MiddlewareAggregatorInterface::CONFIG_NODE_MIDDLEWARE
                )
            );
        }

        return $this->appConfig
            ->get(MiddlewareAggregatorInterface::CONFIG_NODE_MIDDLEWARE);
    }

    /**
     * Aggregate the middleware
     * 
     * @return Traversable
     */
    protected function aggregate(): Traversable
    {
        $middlewareConfigStack = [];

        foreach ($this->getMiddlewareConfig() as $middlewareId => $config) {

            $priority = isset($config['priority']) ? (int)$config['priority'] : 0;

            if (!isset($middlewareConfigStack[$priority])) {
                $middlewareConfigStack[$priority] = [];
            }

            $middlewareConfigStack[$priority][$middlewareId] = $config;
        }

        ksort($middlewareConfigStack);

        foreach ($middlewareConfigStack as $group) {
            foreach ($group as $middlewareId => $middlewareConfig) {
                $this->assertConfig($middlewareConfig, $middlewareId);
                yield $this->createMiddlewareWrapper($middlewareConfig);
            }
        }
    }

    /**
     * Create middleware wrapper
     * 
     * @param array $config
     * @return MiddlewareWrapperInterface
     */
    protected function createMiddlewareWrapper(array $config): MiddlewareWrapperInterface
    {
        $middlewareWrapper = $this
            ->getMiddlewareWrapperPrototype()
            ->setConfig(Config::fromArray($config));

        return $middlewareWrapper;
    }

    /**
     * Get the middleware prototype
     * 
     * @return MiddlewareWrapperInterface
     */
    protected function getMiddlewareWrapperPrototype(): MiddlewareWrapperInterface
    {
        return clone $this->middlewareWrapperPrototype;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): Traversable
    {
        yield from $this->aggregate();
    }

    protected function assertConfig($config, $middlewareId): void
    {
        if (!is_array($config)) {
            throw new \RuntimeException(
                sprintf(
                    'Invalid middleware configuration for middleware ID "%s". Expected array, got %s',
                    $middlewareId,
                    gettype($config)
                )
            );
        }

        if (!isset($config['preference'])) {
            throw new \RuntimeException(
                sprintf(
                    'Missing "preference" in middleware configuration for middleware ID "%s"',
                    $middlewareId
                )
            );
        }
    }
}
