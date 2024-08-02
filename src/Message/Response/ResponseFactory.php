<?php 
namespace Concept\Http\Message\Response;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * @var ResponseInterface|null
     */
    protected ?ResponseInterface $responseInstance = null;

    /**
     * @param ResponseInterface $response
     * @param StreamFactoryInterface $streamFactory
     */
    public function __construct(ResponseInterface $responseInstance)
    {
        $this->responseInstance = $responseInstance;
    }

    /**
     * @return ResponseInterface
     */
    protected function getResponseInstance(): ResponseInterface
    {
        return $this->responseInstance ?? /** non container version */new Response();
    }

    /**
     * {@inheritDoc}
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return $this->getResponseInstance()
            ->withStatus($code, $reasonPhrase);
    }
}