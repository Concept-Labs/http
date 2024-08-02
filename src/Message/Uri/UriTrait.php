<?php
namespace Concept\Http\Message\Uri;

trait UriTrait
{
    
    public function setUri(string $uri): self
    {
        $this->uri = $uri;
        $this->parseUri($uri);

        return $this;
    }

    /**
     * Parse the URI string into components
     * 
     * @param string $uri The URI string
     * 
     * @return static
     */
    protected function parseUri(string $uri): self
    {
        $this->components = parse_url($uri);

        return $this;
    }


    protected function withUri(string $uri): self
    {
        if (strcmp($uri, $this->__toString())) {
            return $this;
        }
        
        $clone = clone $this;
        
        $clone->setUri($uri);

        return $clone;
    }

    protected function withComponents(array $components): self
    {
        $clone = clone $this;
        
        $clone->components = $components;

        return $clone;
    }

    protected function withComponet(int $component, $value): self
    {
        $components =$this->components;
        $components[$component] = $value;

        return $this->withComponents($components);
    }

    protected function getUriString()
    {
        $url = [];

        $url[] = !empty($this->getScheme()) ? $this->getScheme() . '://' : '';

        if (!empty($this->getComponent(PHP_URL_USER))) {
            $url[] = $this->getComponent(PHP_URL_USER);
            if (!empty($this->getComponent(PHP_URL_PASS))) {
                $url[] = ':' . $this->getComponent(PHP_URL_PASS);
            }
            $url[] = '@';
        }

        $url[] = $this->getHost();

        if (null !== $this->getPort()) {
            $url[] = ':' . $this->getPort();
        }

        $url[] = $this->getPath();

        if (!empty($this->getQuery())) {
            $url[] = '?' . $this->getQuery();
        }

        
        if (!empty($this->getFragment())) {
            $url[] = '#' . $this->getFragment();
        }

        return join('', $url);
    }

    /**
     * Get URI component
     *
     * @param integer $component The component (e.g. PHP_URL_SCHEME)
     * 
     * @return string|null The URI component
     */
    protected function getComponent(int $component)
    {
        if (!array_key_exists($component, $this->components)) {
            return null;
        }

        $component = $this->components[$component]
            ?? parse_url($this->uri, $component);
        /**
         * The component may be null from parse_url()
         */    
        //$component = $component ?? '';

        return $component;
    }
}