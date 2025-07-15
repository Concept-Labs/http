<?php

namespace Concept\Http\Middleware;

//use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Concept\App\Exception\RuntimeException;
use Concept\Config\ConfigurableInterface;
use Concept\Config\Traits\ConfigurableTrait;

use Concept\Singularity\Config\ConfigNodeInterface;
use Concept\Singularity\Contract\Lifecycle\PrototypeInterface;
use Concept\Singularity\Factory\FactoryInterface;

class Middleware implements MiddlewareInterface, PrototypeInterface
{
    use ConfigurableTrait;

    private ?FactoryInterface $factory = null;
    private ?MiddlewareInterface $middleware = null;


    /**
     * Dependency injection
     * 
     * @param FactoryInterface $factory
     * @param string $preference
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function __clone()
    {
        $this->middleware = null;
    }

    public function prototype(): static
    {
        return clone $this;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this
            ->getMiddleware()
            ->process($request, $handler);
    }

    /**
     * Get the middleware
     * 
     * @return MiddlewareInterface
     */
    protected function getMiddleware(): MiddlewareInterface
    {
        if ($this->middleware === null) {
            $this->middleware = $this
                ->getFactory()
                ->create(
                    $this->getPreference()
                );
            if ($this->middleware instanceof ConfigurableInterface) {
                $this->middleware = $this->middleware->withConfig($this->getConfig());
            }
                
        }

        return $this->middleware;
    }

    /**
     * Get the factory
     * 
     * @return FactoryInterface
     */
    protected function getFactory(): FactoryInterface
    {
        return $this->factory;
    }

    /**
     * Get the preference
     * 
     * @return string
     */
    protected function getPreference(): string
    {
        if (!$this->getConfig()->has(ConfigNodeInterface::NODE_PREFERENCE)) {
            throw new RuntimeException('No preference set for middleware');
        }
        return $this
            ->getConfig()
            ->get(ConfigNodeInterface::NODE_PREFERENCE);
    }
}
