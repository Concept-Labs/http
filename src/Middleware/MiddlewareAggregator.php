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

        foreach ($this->getMiddlewareConfig() as $config) {

            $priority = isset($config['priority']) ? (int)$config['priority'] : 0;

            if (!isset($middlewareConfigStack[$priority])) {
                $middlewareConfigStack[$priority] = [];
            }

            $middlewareConfigStack[$priority][] = $config;
        }

        ksort($middlewareConfigStack);

        foreach ($middlewareConfigStack as $group) {
            foreach ($group as $middlewareConfig) {
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
        $middleware = $this
            ->getMiddlewareWrapperPrototype();

        $middleware instanceof ConfigurableInterface && 
            $middleware->setConfig(Config::fromArray($config));

        return $middleware;
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
}
