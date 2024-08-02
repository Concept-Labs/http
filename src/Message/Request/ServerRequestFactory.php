<?php
namespace Concept\Http\Message\Request;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * @todo:vg think
     */
    const SERVER_PARAM_SERVERINFO = 'serverInfo';
    const SERVER_PARAM_HEADERS = 'headers';
    const SERVER_PARAM_COOKIES = 'cookie';
    const SERVER_PARAM_QUERY = 'query';
    const SERVER_PARAM_PHP_REQUEST = 'php_request';
    const SERVER_PARAM_FILES = 'files';
    const SERVER_PARAM_BODY = 'body';
    const SERVER_PARAM_PARSED_BODY = 'parsed_body';
    const SERVER_PARAM_ATTRIBUTES = 'attributes';

    /**
     * @var ServerRequestInterface|null|null
     */
    protected ?ServerRequestInterface $serverRequestInstance = null;

    /**
     * @var UriFactoryInterface|null|null
     */
    protected ?UriFactoryInterface $uriFactory = null;

    /**
     * @var StreamFactoryInterface|null|null
     */
    protected ?StreamFactoryInterface $streamFactory = null;

    /**
     * @var array
     */
    protected array $serverParams = [];

    /**
     * @param ServerRequestInterface $serverRequest
     * @param UriFactoryInterface $uriFactory
     */
    public function __construct(
        ServerRequestInterface $serverRequestInstance, 
        UriFactoryInterface $uriFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->serverRequestInstance = $serverRequestInstance;
        $this->uriFactory = $uriFactory;
        $this->streamFactory = $streamFactory;
    }

    /**
     * @param string $method
     * @param mixed $uri
     * @param array $serverParams
     * 
     * @return ServerRequestInterface
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        $this->setServerParams($serverParams);
        $uri = $this->createUri($uri);

        parse_str($uri->getQuery(), $queryParams);

        $serverRequest = $this->getServerRequestInstance()
            ->withMethod($method)
            ->withUri($uri)
            ->withQueryParams($queryParams)
            ->withQueryParams($this->getServerParam(static::SERVER_PARAM_QUERY))
            ->withCookieParams($this->getServerParam(static::SERVER_PARAM_COOKIES))
            ->withUploadedFiles($this->getServerParam(static::SERVER_PARAM_FILES))
            ->withBody($this->createRequestBody())
            ->withParsedBody($this->getServerParam(static::SERVER_PARAM_PARSED_BODY))
        ;

        foreach ($this->getServerParam(static::SERVER_PARAM_ATTRIBUTES) as $key => $value) {
            $serverRequest = $serverRequest->withAttribute($key, $value);
        }

        foreach ($this->getServerParam(static::SERVER_PARAM_HEADERS) as $name => $value) {
            foreach (explode(',', $value) as $val) {
                $serverRequest = $serverRequest->withAddedHeader($name, $val);
            }
        }

        return $serverRequest;
    }

    /**
     * @return UriFactoryInterface
     */
    protected function getUriFactory(): UriFactoryInterface
    {
        return $this->uriFactory;
    }

    /**
     * @return StreamFactoryInterface
     */
    protected function getStreamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory;
    }
    
    /**
     * @return ServerRequestInterface
     */
    protected function getServerRequestInstance(): ServerRequestInterface
    {
        return $this->serverRequestInstance ?? /* non container version*/ new ServerRequest();
    }

    /**
     * @param string $uri
     * 
     * @return UriInterface
     */
    protected function createUri(string $uri): UriInterface
    {
        return $this->getUriFactory()->createUri($uri);
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

    
    /**
     * @param array $serverParams
     * @param bool $globals
     * 
     * @return void
     */
    protected function setServerParams(array $serverParams, ?bool $globals = true): void
    {
        if ($globals === true) {   
            $serverParams[static::SERVER_PARAM_COOKIES] = $serverParams[static::SERVER_PARAM_COOKIES] ?? $_COOKIE;
            $serverParams[static::SERVER_PARAM_QUERY] = $serverParams[static::SERVER_PARAM_QUERY] ?? $_GET;
            $serverParams[static::SERVER_PARAM_PARSED_BODY] = $serverParams[static::SERVER_PARAM_PARSED_BODY] ?? $_POST;
            $serverParams[static::SERVER_PARAM_PHP_REQUEST] = $serverParams[static::SERVER_PARAM_PHP_REQUEST] ?? $_REQUEST;
            $serverParams[static::SERVER_PARAM_FILES] = $serverParams[static::SERVER_PARAM_FILES] ?? $_FILES;
            $serverParams[static::SERVER_PARAM_BODY] = $serverParams[static::SERVER_PARAM_BODY] ?? $this->createRequestBody();
            $serverParams[static::SERVER_PARAM_HEADERS] = $serverParams[static::SERVER_PARAM_HEADERS] ?? getallheaders();
            $serverParams[static::SERVER_PARAM_SERVERINFO] = $serverParams[static::SERVER_PARAM_SERVERINFO] ?? $_SERVER;
            $serverParams[static::SERVER_PARAM_ATTRIBUTES] = $serverParams[static::SERVER_PARAM_ATTRIBUTES] ?? [];
        }

        $this->serverParams = $serverParams;
    }

     /**
     * @return array
     */
    protected function getServerParams(): array
    {
        return $this->serverParams;
    }

    /**
     * @param string $param
     * 
     * @return mixed
     */
    protected function getServerParam(string $param)
    {
        return $this->getServerParams()[$param] ?? [];
    }

   

    
}