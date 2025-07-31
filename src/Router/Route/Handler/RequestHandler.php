<?php
namespace Concept\Http\Router\Route\Handler;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

abstract class RequestHandler implements RequestHandlerInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    protected ?ResponseInterface $response = null;

    /**
     * Dependency injection constructor
     *
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(protected ResponseFactoryInterface $responseFactory)
    {
    }
    
    /**
     * {@inheritDoc}
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response ??= $this->getResponseFactory()->createResponse();
    }

    /**
     * Get the response factory
     *
     * @return ResponseFactoryInterface
     */
    protected function getResponseFactory(): ResponseFactoryInterface
    {
        return $this->responseFactory;
    }

}