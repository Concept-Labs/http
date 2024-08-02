<?php
namespace Concept\Http\Message\Request;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Concept\Http\Message\Message;

class Request extends Message implements RequestInterface
{
    /**
     * @var UriInterface|null
     */
    protected ?UriInterface $uri = null;
    /**
     * @var string
     */
    protected string $method = 'GET';

    /**
     * @var string|null
     */
    protected ?string $requestTarget = null;


    /**
     * {@inheritDoc}
     */
    public function getRequestTarget(): string
    {
        return $this->requestTarget;
    }

    /**
     * {@inheritDoc}
     */
    public function withRequestTarget($requestTarget): RequestInterface
    {
        $new = clone $this;
        $new->requestTarget = $requestTarget;

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * {@inheritDoc}
     */
    public function withMethod($method): RequestInterface
    {
        $new = clone $this;
        $new->method = $method;

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * {@inheritDoc}
     */
    public function withUri(UriInterface $uri, $preserveHost = false): RequestInterface
    {
        $new = clone $this;
        $new->uri = $uri;

        return $new;
    }
}