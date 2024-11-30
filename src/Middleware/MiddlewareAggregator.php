<?php
namespace Concept\Http\Middleware;

//use Psr\Http\Server\MiddlewareInterface;
use Concept\Config\ConfigInterface;
use Concept\Config\ConfigurableInterface;
use Concept\Config\Traits\ConfigurableTrait;
use Concept\Prototype\PrototypableInterface;
use Concept\Prototype\PrototypableTrait;
use Traversable;

class MiddlewareAggregator implements MiddlewareAggregatorInterface, PrototypableInterface
{
    use ConfigurableTrait;
    use PrototypableTrait;

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
        $middlewareStack = [];

        foreach ($this->getConfig() as $id => $middlewareConfigData) {

            $config = $this->getConfig()->withData($middlewareConfigData);

            /**
             * @todo: improve this
             */
            $priority = $config->get('priority');
            while (isset($middlewareStack[$priority])) {
                $priority++;
            }
            
            $middlewareStack[$priority] = $this->createMiddleware($config);
        }

        ksort($middlewareStack);

        foreach ($middlewareStack as $middleware) {
            yield $middleware;
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
