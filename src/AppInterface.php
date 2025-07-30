<?php
namespace Concept\Http;

use Concept\Config\Contract\ConfigurableInterface;
use Concept\EventDispatcher\EventDispatcherAwareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

interface AppInterface extends ConfigurableInterface
{

    /**
     * Run the application.
     */
    public function run(): void;

    /**
     * Add middleware to the stack.
     *
     * @param MiddlewareInterface $middleware
     * @param int $priority
     * @param string|null $id
     * 
     * @return static
     */
    public function addMiddleware(MiddlewareInterface $middleware, int $priority = 100, ?string $id = null ): static;

    /**
     * Set the server request for the application.
     *
     * @param ServerRequestInterface $request
     * @return static
     */
    public function withServerRequest(ServerRequestInterface $request): static;
}