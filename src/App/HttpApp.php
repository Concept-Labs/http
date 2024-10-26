<?php
namespace Concept\Http\App;

use Concept\App\AbstractApp;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Concept\App\AppInterface;
use Concept\Config\Traits\ConfigurableTrait;
use Concept\Http\App\Exception\HttpAppExceptionInterface;
use Concept\Http\RequestHandler\MiddlewareStackHandlerInterface;
use Concept\Http\Response\FlusherInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Throwable;

/**
 * Class HttpApp
 * @package Concept\Http\App
 */
class HttpApp /*extends AbstractApp*/ implements AppInterface
{
    use ConfigurableTrait;

    private ?ServerRequestFactoryInterface $serverRequestFactory = null;
    private ?ServerRequestInterface $serverRequest = null;
    private ?ResponseFactoryInterface $responseFactory = null;
    private ?ResponseInterface $response = null;
    private ?FlusherInterface $flusher = null;
    private ?MiddlewareStackHandlerInterface $middlewareStackHandlerPrototype = null;

    protected array $middlewares = [];

    public function __construct(
        ServerRequestFactoryInterface $serverRequestFactory,
        ResponseFactoryInterface $responseFactory,
        FlusherInterface $flusher,
        MiddlewareStackHandlerInterface $middlewareStackHandlerPrototype
    ) {
        $this->serverRequestFactory = $serverRequestFactory;
        $this->responseFactory = $responseFactory;
        $this->flusher = $flusher;
        $this->middlewareStackHandlerPrototype = $middlewareStackHandlerPrototype;
    }

    /**
     * Set the server request
     * 
     * @param ServerRequestInterface $request
     */
    public function withServerRequest(ServerRequestInterface $request): self
    {
        $clone = clone $this;
        $clone->serverRequest = $request;

        return $clone;
    }

    /**
     * Add middleware to the stack
     * @param MiddlewareInterface $middleware
     */
    public function addMiddleware(MiddlewareInterface $middleware, int $priority = 100): self 
    {
        while (isset($this->middlewares[$priority])) {
            $priority++;
        }
    
        $this->middlewares[$priority] = $middleware;

        ksort($this->middlewares);

        return $this;
    }

    /**
     * Get the middleware stack
     * 
     * @return array
     */
    protected function getMiddlewareStack(): iterable
    {
        foreach ($this->middlewares as $middleware) {
            yield $middleware;
        }
    }

    /**
     * Run the application
     */
    public function run(): void
    {
        try {

            $this->processMiddlewareStack()
                ->flush();

        } catch (HttpAppExceptionInterface $e) {

            //$this->handleAppException($e);

        } catch (Throwable $e) {

            //$this->handleException($e);

        }
    }

    /**
     * Process the middleware stack
     * 
     * @return self
     */
    protected function processMiddlewareStack(): self
    {
        $stackHandler = $this
            ->getMiddlewareStackHandlerPrototype()
            // ->withFinalHandler(
            //     new class implements RequestHandlerInterface {
            //         public function handle(ServerRequestInterface $request): ResponseInterface
            //         {
            //             throw new RuntimeException('No middleware stack configured');
            //         }
            //     }
            // )
            
            ;
        
        foreach ($this->getMiddlewareStack() as $middleware) {
            $stackHandler = $stackHandler->addMiddleware($middleware);
        }

        $this->setResponse(
            $stackHandler->handle(
                $this->getServerRequest()
            )
        );

        return $this;
    }

    /**
     * Initialize the server request
     * 
     * @return ServerRequestInterface
     */
    protected function getServerRequest(): ServerRequestInterface
    {
        if ($this->serverRequest === null) {
            $this->serverRequest = $this->getServerRequestFactory()
                ->createServerRequest(
                    $_SERVER['REQUEST_METHOD'] ?? 'GET',
                    $this->getServerUrl(),
                    $_SERVER
                );
        }

        return $this->serverRequest;
    }

    /**
     * Get the server URL
     * 
     * @return string
     */
    protected function getServerUrl(): string
    {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scheme = $_SERVER['REQUEST_SCHEME'] ?? 'http';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return $scheme . '://' . $host . $uri;
    }

    /**
     * Flush the response
     * 
     * @return self
     */
    protected function flush(): self
    {
        $this
            ->getFlusher()
                ->flush(
                    $this->getResponse()
                );

        return $this;
    }

    /**
     * Get the server request factory
     * 
     * @return ServerRequestFactoryInterface
     */
    protected function getServerRequestFactory(): ServerRequestFactoryInterface
    {
        return $this->serverRequestFactory;
    }

    /**
     * Get the middleware stack handler prototype
     * 
     * @return MiddlewareStackHandlerInterface
     */
    protected function getMiddlewareStackHandlerPrototype(): MiddlewareStackHandlerInterface
    {
        return clone $this->middlewareStackHandlerPrototype;
    }

    /**
     * Get the response factory
     * 
     * @return ResponseFactoryInterface
     */
    protected function getResponseFactory(): ResponseFactoryInterface
    {
        return $this->responseFactory;
    }

    /**
     * Get the response
     * 
     * @return ResponseInterface
     */
    protected function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Set the response
     * 
     * @param ResponseInterface $response
     * 
     * @return self
     */
    protected function setResponse(ResponseInterface $response): self
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get the flusher
     * @return FlusherInterface
     */
    protected function getFlusher(): FlusherInterface
    {
        return $this->flusher;
    }

    protected function handleAppException(HttpAppExceptionInterface $e): void
    {
        /**
         * @todo Implement this method
         */
        $response = $this->getResponseFactory()->createResponse(500);
        $response->getBody()->write($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        $this->getFlusher()->flush($response);
    }

    protected function handleException(Throwable $e): void
    {
        /**
         * @todo Implement this method
         */
        $response = $this->getResponseFactory()->createResponse(500);
        $response->getBody()->write('Internal Server Error:' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
        $this->getFlusher()->flush($response);
    }

}
