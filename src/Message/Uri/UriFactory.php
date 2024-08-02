<?php
namespace Concept\Http\Message\Uri;

use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class UriFactory implements UriFactoryInterface
{
 
    /**
     * @var UriInterface The Uri instance
     */
    protected ?UriInterface $uriInstance = null;

    /**
     * @var string The uri string
     */
    protected string $uriString = '';

    /**
     * The constructor
     *
     * @param UriInterface $uri The uri instance
     */
    public function __construct(UriInterface $uriInstance)
    {
        $this->uriInstance = $uriInstance;
    }

    /**
     * {@inheritDoc}
     */
    public function createUri(string $uri = ''): UriInterface
    {
        $this->uriString = $uri;

        return $this->getUriInstance()
            ->withScheme($this->getComponent(PHP_URL_SCHEME) ?? '')
            ->withUserInfo($this->getComponent(PHP_URL_USER) ?? '', $this->getComponent(PHP_URL_PASS) ?? '')
            ->withHost($this->getComponent(PHP_URL_HOST) ?? '')
            ->withPort($this->getComponent(PHP_URL_PORT) ?? null)
            ->withPath($this->getComponent(PHP_URL_PATH) ?? '')
            ->withQuery($this->getComponent(PHP_URL_QUERY) ?? '')
            ->withFragment($this->getComponent(PHP_URL_FRAGMENT) ?? '');
    }

    /**
     * Get the injected uri instance
     * 
     * @return UriInterface
     */
    protected function getUriInstance(): UriInterface
    {
        return $this->uriInstance ?? /** non container version */ new Uri();
    }

    /**
     * Get the uri string
     * 
     * @return string
     */
    protected function getUriString(): string
    {
        return $this->uriString;
    }

    /**
     * Get the uri component
     * 
     * @param int $component The component @see PHP_URL_*
     * 
     * @return string|null
     */
    protected function getComponent(int $component)
    {
        return parse_url($this->getUriString(), $component);
    }

   
}