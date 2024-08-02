<?php
namespace Concept\Http\Message\Request;

use InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class RequestFactory implements RequestFactoryInterface
{

    /**
     * @var RequestInterface|null
     */
    protected ?RequestInterface $requestInstance = null;
    
    /**
     * @var UriFactoryInterface|null
     */
    protected ?UriFactoryInterface $uriFactory = null;

    /**
     * @var StreamFactoryInterface|null|null
     */
    protected ?StreamFactoryInterface $streamFactory = null;

    /**
     * @param RequestInterface $request
     * @param UriFactoryInterface $uriFactory
     */
    public function __construct(UriFactoryInterface $uriFactory, StreamFactoryInterface $streamFactory, RequestInterface $requestInstance)
    {
        $this->uriFactory = $uriFactory;
        $this->streamFactory = $streamFactory;
        $this->requestInstance = $requestInstance;
    }

    /**
     * @param string $method
     * @param mixed $uri
     * 
     * @return RequestInterface
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        $uri = is_string($uri) ? $this->getUriFactory()->createUri($uri) : $uri;

        if (!$uri instanceof UriInterface) {
            throw new InvalidArgumentException('Invalid uri provided');
        }

        return $this->getRequestInstance()
            ->withMethod($method)
            ->withRequestTarget('')
            ->withUri($uri)
            ->withBody($this->createRequestBody())
            ;

    }

    /**
     * @return UriFactoryInterface
     */
    protected function getUriFactory(): UriFactoryInterface
    {
        return $this->uriFactory;
    }

    /**
     * @return RequestInterface
     */
    protected function getRequestInstance(): RequestInterface
    {
        return $this->requestInstance ?? /** non container version*/new Request(); 
    }

    /**
     * @return StreamFactoryInterface
     */
    protected function getStreamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory;
    }

    /**
     * @return StreamInterface
     */
    protected function createRequestBody(): StreamInterface
    {
        return $this->getStreamFactory()
            ->createStreamFromResource(
                fopen('php://input', 'r+')
            );
    }

}