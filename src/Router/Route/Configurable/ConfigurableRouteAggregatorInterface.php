<?php
namespace Concept\Http\Router\Route\Configurable;

use Concept\Config\ConfigInterface;
use Concept\Http\Router\Route\RouteAggregatorInterface;
use IteratorAggregate;

interface ConfigurableRouteAggregatorInterface extends RouteAggregatorInterface, IteratorAggregate
{
    public function withConfig(ConfigInterface $config): self;
}