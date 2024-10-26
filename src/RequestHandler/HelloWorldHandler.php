<?php

namespace Concept\Http\RequestHandler;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HelloWorldHandler implements RequestHandlerInterface
{
    protected ?ResponseFactoryInterface $responseFactory = null;

    public function __construct(
        ResponseFactoryInterface $responseFactory
    )
    {
        $this->responseFactory = $responseFactory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();
        $response->getBody()->write('Hello, World!');

        return $response;
    }
}
