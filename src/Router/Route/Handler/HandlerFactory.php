<?php
namespace Concept\Http\Router\Route\Handler;

use Psr\Http\Server\RequestHandlerInterface;
use Concept\Config\Contract\ConfigurableTrait;
use Concept\Singularity\Config\ConfigNodeInterface;
use Concept\Singularity\Factory\ServiceFactory;

class HandlerFactory extends ServiceFactory implements HandlerFactoryInterface
{
    use ConfigurableTrait;

    /**
     * Create a request handler instance
     * 
     * Creates a handler service based on the configured preference.
     * The handler must implement RequestHandlerInterface.
     * 
     * @param array<string, mixed> $args Additional arguments for handler creation
     * @return RequestHandlerInterface The created handler instance
     */
    public function create(array $args = []): RequestHandlerInterface
    {
        $handler = $this->createService(
            $this->getHandlerPreference()
        );
        
        return $handler;
    }

    /**
     * Get the handler preference from configuration
     * 
     * @return string The fully qualified handler class name
     * @throws \RuntimeException If preference is not set
     */
    protected function getHandlerPreference(): string
    {
        return $this
            ->getConfig()
            ->get(ConfigNodeInterface::NODE_PREFERENCE) ?? throw new \RuntimeException("Handler preference not set");
    }

}