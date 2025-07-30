<?php
namespace Concept\Http\Router\Route\Handler;

interface SimpleRequestHandlerInterface extends \Psr\Http\Server\RequestHandlerInterface
{

    /**
     * Set the response status code
     *
     * @param int $statusCode
     * @return self
     */
    public function status(int $statusCode): self;

    /**
     * Set a header for the response
     *
     * @param string $name
     * @param string $value
     * @return self
     */
    public function header(string $name, string $value): self;

    /**
     * Set the response body
     *
     * @param string $body
     * @return self
     */
    public function body(string $body): self;

}