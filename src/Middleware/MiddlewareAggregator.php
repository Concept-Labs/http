<?php
namespace Concept\Http\Middleware;

//use Psr\Http\Server\MiddlewareInterface;

use Concept\Config\Config;
use Concept\Config\ConfigInterface;
use Concept\Config\Contract\ConfigurableInterface;
use Concept\Config\Contract\ConfigurableTrait;
use Traversable;

class MiddlewareAggregator implements MiddlewareAggregatorInterface
{
    use ConfigurableTrait;

    private ?MiddlewareWrapperInterface $middlewareWrapperPrototype = null;

    /**
     * Dependency injection
     * 
     * @param MiddlewareWrapperInterface $middleware
     */
    public function __construct(MiddlewareWrapperInterface $middlewareWrapper)
    {
        $this->middlewareWrapperPrototype = $middlewareWrapper;
    }


    /**
     * Aggregate the middleware
     * 
     * @return Traversable
     */
    protected function aggregate(): Traversable
    {
        $middlewareConfigStack = [];

        foreach ($this->getConfig() as $id => $config) {
            

            /**
             * @todo: improve this
             */
            $priority = (int)$config['priority'];
            while (isset($middlewareConfigStack[$priority])) {
                $priority++;
            }

            $middlewareConfigStack[$priority] = $config;
        }

        ksort($middlewareConfigStack);

        foreach ($middlewareConfigStack as $middlewareConfig) {
            yield $this->createMiddlewareWrapper($middlewareConfig);
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

        if ($middleware instanceof ConfigurableInterface) {
            /**
             * @important!
             * @todo: service factory will do this?
             */
            $middleware = $middleware->setConfig(Config::fromArray($config));
        }

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
