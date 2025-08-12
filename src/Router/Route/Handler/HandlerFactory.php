<?php
namespace Concept\Http\Router\Route\Handler;

use Psr\Http\Server\RequestHandlerInterface;
use Concept\Config\Contract\ConfigurableInterface;
use Concept\Config\Contract\ConfigurableTrait;
use Concept\Singularity\Config\ConfigNodeInterface;
use Concept\Singularity\Factory\ServiceFactory;

class HandlerFactory extends ServiceFactory implements HandlerFactoryInterface
{
    use ConfigurableTrait;

    /**
     * {@inheritDoc}
     */
    public function create(array $args = []): RequestHandlerInterface
    {
        $handler = $this->createService(
                $this->getHandlerPreference()
            );

            /**
             @todo remove because router sets config on handler
             */
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


}