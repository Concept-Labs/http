<?php
namespace Concept\Http\Middleware\Configurable;

use Concept\Config\ConfigInterface;
use Concept\Http\Middleware\MiddlewareAggregatorInterface;

interface ConfigurableMiddlewareAggregatorInterface extends MiddlewareAggregatorInterface
{
    /**
     * Set the config
     * 
     * @param ConfigInterface $config
     * 
     * @return self
     */
    public function withConfig(ConfigInterface $config): self;
    
}
