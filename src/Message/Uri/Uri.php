<?php
namespace Concept\Http\Message\Uri;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    use UriTrait;
    
    /**
     * @var array<string, int> Schemes with default ports
     */
    const SCHEMES = [
        'http' => 80,
        'https' => 443,
        'ftp' => 21,
        'ssh' => 22,
        'sftp' => 22,
        'telnet' => 23,
        'smtp' => 25,
        'ldap' => 389,
        'rtsp' => 554,
    ];
    
    /**
     * @var string The URI string
     */
    protected string $uri = '';

    /**
     * @var array<int, string> The URI components
     */
    protected array $components = [];

    /**
     * {@inheritDoc}
     */
    public function getScheme()
    {
        return $this->getComponent(PHP_URL_SCHEME);
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthority()
    {
        $userInfo = $this->getUserInfo();
        $host = $this->getHost();
        $port = $this->getPort();

        if (empty($userInfo) && empty($host)) {
            return '';
        }
        
        $authority = '';

        if (!empty($userInfo)) {
            $authority .= $userInfo . '@';
        }

        $authority .= $host;

        if (!empty($port)) {
            $authority .= ':' . $port;
        }
        
        return $authority;
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInfo()
    {
        $user = $this->getComponent(PHP_URL_USER);
        $password = $this->getComponent(PHP_URL_PASS);

        if (empty($user)) {
            return '';
        }

        return $password !== null 
            ? sprintf('%s:%s', $user, $password) 
            : $user;
    }

    /**
     * {@inheritDoc}
     */
    public function getHost()
    {
        return $this->getComponent(PHP_URL_HOST);
    }

    
    /**
     * {@inheritDoc}
     */
    public function getPort()
    {
        $scheme = $this->getScheme();
        $port = $this->getComponent(PHP_URL_PORT);

        if (empty($scheme) || empty($port)) {
            return null;
        }

        $defaultPort = $this->getDefaultPort($scheme);

        return $port === $defaultPort ? null : (int)$port;
    }

    /**
     * Get a default scheme port
     *
     * @param string $scheme The scheme string
     * 
     * @return int|null The default port or null if not found
     */
    public function getDefaultPort(string $scheme)
    {
        
        return array_key_exists($scheme, static::SCHEMES)
            ? static::SCHEMES[$scheme] 
            : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath()
    {
        return $this->getComponent(PHP_URL_PATH) ?? '';
    }

    /**
     * {@inheritDoc}
     */
    public function getQuery()
    {
        $query = $this->getComponent(PHP_URL_QUERY);

        return !empty($query) ? rawurldecode($query) : '';
    }


    /**
     * {@inheritDoc}
     */
    public function getFragment()
    {
        $fragment = $this->getComponent(PHP_URL_FRAGMENT);
    
        return !empty($fragment) ? rawurldecode($fragment) : '';
    }
    
    /**
     * {@inheritDoc}
     */
    public function withScheme(string $scheme)
    {
        if (!array_key_exists(strtolower($scheme), static::SCHEMES)) {
            throw new \InvalidArgumentException('Invalid or unsupported scheme');
        }
    
        return $this->withComponet(PHP_URL_SCHEME, $scheme);
    }

    /**
     * {@inheritDoc}
     */
    public function withUserInfo(string $user, ?string $password = null)
    {
        return $this->withComponet(PHP_URL_USER, $user)
            ->withComponet(PHP_URL_PASS, $password);
    }

    /**
     * {@inheritDoc}
     */
    public function withHost(string $host)
    {
        if (empty($host)) {
            throw new \InvalidArgumentException('Host cannot be empty');
        }

        return $this->withComponet(PHP_URL_HOST, $host);
    }

    /**
     * {@inheritDoc}
     */
    public function withPort(?int $port)
    {
        if ($port !== null && ($port < 1 || $port > 65535)) {
            throw new \InvalidArgumentException(_('Invalid port number'));
        }

        return $this->withComponet(PHP_URL_PORT, $port);
    }

    /**
     * {@inheritDoc}
     */
    public function withPath(string $path)
    {
        if ($path !== '' && $path[0] !== '/' && strpos($path, '://') === false) {
            throw new \InvalidArgumentException('Invalid path: ' . $path);
        }

        return $this->withComponet(PHP_URL_PATH, $path);
    }

    /**
     * {@inheritDoc}
     */
    public function withQuery(string $query)
    {
        return $this->withComponet(PHP_URL_QUERY, $query);
    }

    /**
     * {@inheritDoc}
     */
    public function withFragment(string $fragment)
    {
        return $this->withComponet(PHP_URL_FRAGMENT, $fragment);
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return $this->getUriString();
    }
}