<?php

namespace Concept\Http\Request;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class Factory 
{
    protected ?ServerRequestFactoryInterface $serverRequestFactory = null;

    public function __construct(
        ServerRequestFactoryInterface $serverRequestFactory
    ) {
        $this->serverRequestFactory = $serverRequestFactory;
    }

    public function createServerRequest(): ServerRequestInterface
    {
        return $this->getServerRequestFactoryInstance()->createServerRequest(
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_URI'],
            $_SERVER
        );
    }

    protected function getServerRequestFactoryInstance(): ServerRequestFactoryInterface
    {
        return clone $this->serverRequestFactory;
    }
}