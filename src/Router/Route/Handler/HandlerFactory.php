<?php
namespace Concept\Http\Router\Route\Handler;

use Concept\Config\Contract\ConfigurableInterface;
use Concept\Config\Contract\ConfigurableTrait;
use Concept\Singularity\Config\ConfigNodeInterface;
use Concept\Singularity\Factory\FactoryInterface;
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
            $handler = $handler->setConfig($this->getConfig());
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
            ->get(ConfigNodeInterface::NODE_PREFERENCE);
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