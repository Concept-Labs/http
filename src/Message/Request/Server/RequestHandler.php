<?php
namespace Concept\Http\Message\Request\Server;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class RequestHandler implements RequestHandlerInterface
{
    /**
     * @var ResponseFactoryInterface|null|null
     */
    protected ?ResponseFactoryInterface $responseFactory = null;

    /**
     * @var ResponseInterface|null|null
     */
    protected ?ResponseInterface $response = null;
    
    /**
     * {@inheritDoc}
     */
    abstract public function handle(ServerRequestInterface $request): ResponseInterface;

    /**
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param int $code
     * @param string $reasonPhrase
     * 
     * @return ResponseInterface
     */
    protected function getResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        if ($this->response === null || $this->response->getStatusCode() !== $code || $this->response->getReasonPhrase() !== $reasonPhrase) {
            $this->response = $this->responseFactory->createResponse($code, $reasonPhrase);
        }

        return $this->response;
    }
}