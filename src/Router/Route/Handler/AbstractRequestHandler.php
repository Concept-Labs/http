<?php
namespace Concept\Http\Router\Route\Handler;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractSimpleRequestHandler implements SimpleRequestHandlerInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    private ?ResponseInterface $response = null;

    /**
     * Dependency injection constructor
     *
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(private ResponseFactoryInterface $responseFactory)
    {
    }
    
    /**
     * Get the response object
     *
     * @return ResponseInterface
     */
    protected function getResponse(): ResponseInterface
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

    /**
     * {@inheritDoc}
     */
    public function status(int $code): self
    {
        $this->response = $this->getResponse()->withStatus($code);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function header(string $name, string $value): self
    {
        $this->response = $this->getResponse()->withHeader($name, $value);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function body(string $body): self
    {
        $this->getResponse()
            ->getBody()
            ->write($body);

        return $this;
    }

}