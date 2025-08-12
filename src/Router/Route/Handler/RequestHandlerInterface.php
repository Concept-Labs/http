<?php
namespace Concept\Http\Router\Route\Handler;

use Concept\Config\Contract\ConfigurableInterface;
use Psr\Http\Message\ResponseInterface;

interface RequestHandlerInterface extends \Psr\Http\Server\RequestHandlerInterface, ConfigurableInterface
{
    /**
     * Get the response object
     *
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface;
    
}