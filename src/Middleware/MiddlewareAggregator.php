<?php
namespace Concept\Http\Middleware;

//use Psr\Http\Server\MiddlewareInterface;
use Concept\Config\ConfigInterface;
use Concept\Config\ConfigurableInterface;
use Concept\Config\Traits\ConfigurableTrait;
use Traversable;

class MiddlewareAggregator implements MiddlewareAggregatorInterface
{
    use ConfigurableTrait;

    private ?MiddlewareInterface $middlewarePrototype = null;

    /**
     * Dependency injection
     * 
     * @param MiddlewareInterface $middleware
     */
    public function __construct(MiddlewareInterface $middleware)
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
            $config = $this->getConfig()->withData($middlewareConfigData);

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
     * @return MiddlewareInterface
     */
    protected function createMiddleware(ConfigInterface $config): MiddlewareInterface
    {
        $middleware = $this
            ->getMiddlewarePrototype();

        if ($middleware instanceof ConfigurableInterface) {
            /**
             * @important!
             * @todo: service factory will do this?
             */
            $middleware = $middleware->withConfig($config);
        }

        return $middleware;
    }

    /**
     * Get the middleware prototype
     * 
     * @return MiddlewareInterface
     */
    protected function getMiddlewarePrototype(): MiddlewareInterface
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
