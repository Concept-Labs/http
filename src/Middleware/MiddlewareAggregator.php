<?php
namespace Concept\Http\Middleware;

//use Psr\Http\Server\MiddlewareInterface;
use Concept\Config\ConfigInterface;
use Concept\Config\Contract\ConfigurableInterface;
use Concept\Config\Contract\ConfigurableTrait;
use Traversable;

class MiddlewareAggregator implements MiddlewareAggregatorInterface
{
    use ConfigurableTrait;

    private ?MiddlewareWrapperInterface $middlewarePrototype = null;

    /**
     * Dependency injection
     * 
     * @param MiddlewareWrapperInterface $middleware
     */
    public function __construct(MiddlewareWrapperInterface $middleware)
    {
        $this->middlewarePrototype = $middleware;
    }


    /**
     * Aggregate the middleware
     * 
     * @return Traversable
     */
    protected function aggregate(): Traversable
    {
        $middlewareConfigStack = [];

        foreach ($this->getConfig() as $id => $middlewareConfigData) {
            $config = $this->getConfig()->hydrate($middlewareConfigData);

            /**
             * @todo: improve this
             */
            $priority = (int)$config->get('priority');
            while (isset($middlewareStack[$priority])) {
                $priority++;
            }

            $middlewareConfigStack[$priority] = $config;
        }

        ksort($middlewareConfigStack);

        foreach ($middlewareConfigStack as $middlewareConfig) {
            yield $this->createMiddleware($middlewareConfig);
        }
    }

    /**
     * Create middleware wrapper
     * 
     * @param ConfigInterface $config
     * @return MiddlewareWrapperInterface
     */
    protected function createMiddleware(ConfigInterface $config): MiddlewareWrapperInterface
    {
        $middleware = $this
            ->getMiddlewarePrototype();

        if ($middleware instanceof ConfigurableInterface) {
            /**
             * @important!
             * @todo: service factory will do this?
             */
            $middleware = $middleware->setConfig($config);
        }

        return $middleware;
    }

    /**
     * Get the middleware prototype
     * 
     * @return MiddlewareWrapperInterface
     */
    protected function getMiddlewarePrototype(): MiddlewareWrapperInterface
    {
        return clone $this->middlewarePrototype;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): Traversable
    {
        yield from $this->aggregate();
    }
}
