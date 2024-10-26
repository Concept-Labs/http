<?php
namespace Concept\Http\Router\Route;

use Concept\Config\ConfigInterface;

interface RouteAggregatorInterface
{
    /**
     * Set the configuration for the router.
     *
     * @param ConfigInterface $config
     * @return self
     */
    public function withConfig(ConfigInterface $config): self;

    /**
     * Aggregate the routes.
     *
     * @return iterable
     */
    public function aggregate(): iterable;
}