<?php
namespace Concept\Http\Middleware\Configurable;

use Psr\Http\Server\MiddlewareInterface;
use Concept\Config\ConfigInterface;
use Concept\Config\Traits\ConfigurableTrait;
use IteratorAggregate;
use Traversable;

class ConfigurableMiddlewareAggregator implements ConfigurableMiddlewareAggregatorInterface, IteratorAggregate
{
    use ConfigurableTrait;

    private ?ConfigurableMiddleware $configurableMiddlewarePrototype = null;

    /**
     * Dependency injection
     * 
     * @param ConfigurableMiddlewareInterface $configurableMiddleware
     */
    public function __construct(ConfigurableMiddlewareInterface $configurableMiddleware)
    {
        $this->configurableMiddlewarePrototype = $configurableMiddleware;
    }

    /**
     * {@inheritDoc}
     */
    public function aggregate(): iterable
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
        return $this
            ->getConfigurableMiddlewarePrototype()
            ->withConfig($config);
    }

    /**
     * Get the configurable middleware prototype
     * 
     * @return ConfigurableMiddlewareInterface
     */
    protected function getConfigurableMiddlewarePrototype(): ConfigurableMiddlewareInterface
    {
        return clone $this->configurableMiddlewarePrototype;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): Traversable
    {
        yield from $this->aggregate();
    }
}
