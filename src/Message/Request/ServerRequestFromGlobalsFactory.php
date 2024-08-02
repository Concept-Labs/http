<?php
namespace Concept\Http\Message\Request;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\UriInterface;

class ServerRequestFromGlobalsFactory extends ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * @param array $serverParams
     * @param bool|null $globals
     * 
     * @return void
     */
    protected function setServerParams(array $serverParams = [], ?bool $globals = null): void
    {
        $serverParams[static::SERVER_PARAM_SERVERINFO] = $_SERVER;
        $serverParams[static::SERVER_PARAM_HEADERS] = getallheaders();
        $serverParams[static::SERVER_PARAM_COOKIES] = $_COOKIE;
        $serverParams[static::SERVER_PARAM_QUERY] = $_REQUEST;
        //$serverParams[static::SERVER_PARAM_QUERY] = $_GET;
        $serverParams[static::SERVER_PARAM_FILES] = $_FILES;
        $serverParams[static::SERVER_PARAM_PARSED_BODY] = $_POST;
        //$serverParams[static::SERVER_PARAM_BODY] = ''; 
        $serverParams[static::SERVER_PARAM_ATTRIBUTES] = $serverParams[static::SERVER_PARAM_ATTRIBUTES] ?? [];

        $this->serverParams = $serverParams;
    }

    /**
     * @param string|null $uri
     * 
     * @return UriInterface
     */
    protected function createUri(string $uri = null): UriInterface
    {
        return $this->getUriFactory()->createUri(
            $this->getUrlStringFromGlobals()
        );
    }


    /**
     * @return string
     */
    protected function getUrlStringFromGlobals(): string
    {
        
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';

        $user = !empty($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
        $pass = !empty($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';

        $domain = $_SERVER['HTTP_HOST'];

        $port = parse_url('http://' . $domain, PHP_URL_PORT);
        $hostname = parse_url('http://' . $domain, PHP_URL_HOST);

        if (!empty($port) && $port !== 80 && $port !== 443) {
            $hostname .= ':' . $port;
        }

        $path = $_SERVER['REQUEST_URI'];

        $user_info = ($user !== '') ? $user : '';
        if ($user_info && $pass) {
            $user_info .= ':' . $pass . '@';
        } elseif ($user_info) {
            $user_info .= '@';
        }

        $url = $protocol . $user_info . $hostname . $path;

        return $url;
    }

}