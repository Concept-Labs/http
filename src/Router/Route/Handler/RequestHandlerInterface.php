<?php
namespace Concept\Http\Router\Route\Handler;

use Psr\Http\Message\ResponseInterface;

interface RequestHandlerInterface extends \Psr\Http\Server\RequestHandlerInterface
{

    /**
     * Get the response object
     *
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface;
    
}