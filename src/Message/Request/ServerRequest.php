<?php
namespace Concept\Http\Message\Request;

use Psr\Http\Message\ServerRequestInterface;

class ServerRequest extends Request implements ServerRequestInterface
{

    /**
     * @var array
     */
    protected array $serverParams = [];
    /**
     * @var array
     */
    protected array $cookieParams = [];
    /**
     * @var array
     */
    protected array $queryParams = [];
    /**
     * @var array
     */
    protected array $uploadedFiles = [];
    /**
     * @var array
     */
    protected array $parsedBody = [];
    /**
     * @var array
     */
    protected array $attributes = [];
    

    
    /**
     * {@inheritDoc}
     */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    /**
     * {@inheritDoc}
     */
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    /**
     * {@inheritDoc}
     */
    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $new = clone $this;
        $new->cookieParams = $cookies;

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getQueryParams(): array
    {
        
        return $this->queryParams;
    }

    /**
     * {@inheritDoc}
     */
    public function withQueryParams(array $query): ServerRequestInterface
    {
        $new = clone $this;
        $new->queryParams = $query;

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /**
     * {@inheritDoc}
     */
    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        $new = clone $this;
        $new->uploadedFiles = $uploadedFiles;

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * {@inheritDoc}
     */
    public function withParsedBody($data): ServerRequestInterface
    {
        $new = clone $this;
        $new->parsedBody = $data;

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttribute($name, $default = null)
    {
        return $this->attributes[$name] ?? $default;
    }

    /**
     * {@inheritDoc}
     */
    public function withAttribute($name, $value): ServerRequestInterface
    {
        $new = clone $this;
        $new->attributes[$name] = $value;

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withoutAttribute($name): ServerRequestInterface
    {
        $new = clone $this;
        unset($new->attributes[$name]);

        return $new;
    }
}