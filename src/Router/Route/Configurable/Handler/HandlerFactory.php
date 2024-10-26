<?php
namespace Concept\Http\Router\Route\Configurable\Handler;

use Concept\Config\ConfigurableInterface;
use Concept\Config\Traits\ConfigurableTrait;
use Concept\Di\Factory\DiFactoryInterface;
use Concept\Factory\FactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HandlerFactory implements HandlerFactoryInterface
{
    use ConfigurableTrait;

    private ?FactoryInterface $factory = null;

    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritDoc}
     */
    public function create(): RequestHandlerInterface
    {
        $handler = $this
            ->getFactory()
            ->create(
                $this->getHandlerPreference()
            );

        if (!($handler instanceof RequestHandlerInterface)) {
            throw new \RuntimeException('Route handler must implement RequestHandlerInterface');
        }
        
        if ($handler instanceof ConfigurableInterface) {
            $handler = $handler->withConfig($this->getConfig());
        }
        
        return $handler;
    }

    /**
     * Get the handler preference
     * 
     * @return string
     */
    protected function getHandlerPreference(): string
    {
        return $this
            ->getConfig()
            ->get(DiFactoryInterface::NODE_PREFERENCE);
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

}