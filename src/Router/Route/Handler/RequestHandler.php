<?php
namespace Concept\Http\Router\Route\Handler;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

abstract class RequestHandler implements RequestHandlerInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    private ?ResponseFactoryInterface $responseFactory = null;
    protected ?ResponseInterface $response = null;

    /**
     * {@inheritDoc}
     */
    public function withResponseFactory(ResponseFactoryInterface $responseFactory): static
    {
        $this->responseFactory = $responseFactory;

        return $this;
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
        return $this->responseFactory ?? throw new \RuntimeException('Response factory not set.');
    }

}