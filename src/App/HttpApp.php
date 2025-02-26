<?php
namespace Concept\Http\App;

use Throwable;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Concept\App\AbstractApp;
use Concept\App\AppInterface;
use Concept\Config\Traits\ConfigurableTrait;
use Concept\EventDispatcher\EventBusInterface;
use Concept\Http\App\Exception\HttpAppExceptionInterface;
use Concept\Http\RequestHandler\MiddlewareStackHandlerInterface;
use Concept\Http\Response\FlusherInterface;
use Concept\Config\ConfigInterface;
use Concept\Http\App\Event\StartEvent;
/**
 * Class HttpApp
 * @package Concept\Http\App
 */

class HttpApp extends AbstractApp implements AppInterface
{

    public function __invoke(StartEvent $event)
    {
        $this->handleEvent($event);
    }

    public function handleEvent(StartEvent $event)
    {
        echo "<h1>Event handled</h1><pre>";
        
    }

    use ConfigurableTrait;

    
    private ?ServerRequestInterface $serverRequest = null;
    private ?ResponseInterface $response = null;

    protected array $middlewares = [];

    public function __construct(
        private ServerRequestFactoryInterface $serverRequestFactory,
        private ResponseFactoryInterface $responseFactory,
        private FlusherInterface $flusher,
        private MiddlewareStackHandlerInterface $middlewareStackHandlerPrototype,
        private EventBusInterface $eventBus
    ) {
    }

    /**
     * Set the server request
     * 
     * @param ServerRequestInterface $request
     */
    public function withServerRequest(ServerRequestInterface $request): static
    {
        $clone = clone $this;
        $clone->serverRequest = $request;

        return $clone;
    }

    /**
     * Add middleware to the stack
     * @param MiddlewareInterface $middleware
     */
    public function addMiddleware(MiddlewareInterface $middleware, int $priority = 100): static 
    {
        $this->middlewares[$priority][] = $middleware;

        return $this;
    }

    /**
     * Get the middleware stack
     * 
     * @return array
     */
    protected function getMiddlewareStack(): iterable
    {
        ksort($this->middlewares);
        foreach ($this->middlewares as $priority => $middlewares) {
            foreach ($middlewares as $middleware) {
                yield $middleware;
            }
        }
    }

    public function configure(ConfigInterface $config): static
    {
        $this->setConfig($config);

        return $this;
    }

    /**
     * Run the application
     */
    public function run(): void
    {
        try {
            // $this->eventBus
            //     ->dispatch(
            //         (new AppRunBefore())->attach('app', $this)
            // );
            $this->processMiddlewareStack()
//                ->flush() //use response middleware instead
                ;

        } catch (HttpAppExceptionInterface $e) {

            $this->handleAppException($e);

        } catch (Throwable $e) {

            $this->handleException($e);

        }
    }

    /**
     * Process the middleware stack
     * 
     * @return static
     */
    protected function processMiddlewareStack(): static
    {
        $stackHandler = $this
            ->getMiddlewareStackHandlerPrototype()
            ->withFinalHandler(
                new class implements RequestHandlerInterface {
                    public function handle(ServerRequestInterface $request): ResponseInterface {
                        throw new \RuntimeException('No response generated. Check middleware stack.');
                    }
                }
            );
        
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
        return $this->serverRequest ??= $this->getServerRequestFactory()
            ->createServerRequest(
                $_SERVER['REQUEST_METHOD'] ?? 'GET',
                $this->getServerUrl(),
                $_SERVER
            );
    }

    /**
     * Get the server URL
     * @todo: improve this
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
     * @return static
     */
    protected function flush(): static
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
     * @return static
     */
    protected function setResponse(ResponseInterface $response): static
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

    /**
     * Handle application exception
     * 
     * @param HttpAppExceptionInterface $e
     */
    protected function handleAppException(HttpAppExceptionInterface $e): void
    {
        /**
         * @todo Implement this method
         */
        $response = $this->getResponseFactory()->createResponse(500);
        $response->getBody()->write($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        $this->getFlusher()->flush($response);
    }

    /**
     * Handle exception
     * 
     * @param Throwable $e
     */
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
