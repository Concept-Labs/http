<?php

namespace Concept\Http\Request\Collector;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Collector
 * @package Concept\Http\Request\Collector
 */
class Collector
{
    /**
     * @var ServerRequestFactoryInterface|null
     */
    protected ?ServerRequestFactoryInterface $serverRequestFactory = null;

    /**
     * @var ServerRequestInterface|null
     */
    protected ?ServerRequestInterface $serverRequest = null;

    /**
     * Collector constructor.
     *
     * @param ServerRequestFactoryInterface $serverRequestFactory
     */
    public function __construct(ServerRequestFactoryInterface $serverRequestFactory)
    {
        $this->serverRequestFactory = $serverRequestFactory;
    }

    /**
     * Collect the server request.
     *
     * @return ServerRequestInterface
     */
    public function collect(): ServerRequestInterface
    {
        if ($this->serverRequest === null) {
            $this->serverRequest = $this->createServerRequest();
        }
        return $this->serverRequest;
    }

    /**
     * Create a server request.
     *
     * @return ServerRequestInterface
     */
    protected function createServerRequest(): ServerRequestInterface
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        return $this->getServerRequestFactoryInstance()->createServerRequest(
            $method,
            $uri,
            $_SERVER
        );
    }

    /**
     * Get a cloned instance of the server request factory.
     *
     * @return ServerRequestFactoryInterface
     */
    protected function getServerRequestFactoryInstance(): ServerRequestFactoryInterface
    {
        return clone $this->serverRequestFactory;
    }
}