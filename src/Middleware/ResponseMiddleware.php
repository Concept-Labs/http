<?php
namespace Concept\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Concept\Config\Contract\ConfigurableTrait;
use Concept\Http\App\Exception\RuntimeException;

class ResponseMiddleware implements ResponseMiddlewareInterface
{
    use ConfigurableTrait;

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        echo "RESPONSE MIDDLEWARE PROCESSING\n";
        //$response = $handler->handle($request);
        
        //$this->flush($response);

        //return $response;

        return $handler->handle($request);
    }

    /**
     * {@inheritDoc}
     */
    public function flush(ResponseInterface $response): void
    {
        $this
            ->sendHeaders($response)
            ->sendBody($response);
    }

    /**
     * Send the response headers.
     *
     * @param ResponseInterface $response
     * @return static
     */
    protected function sendHeaders(ResponseInterface $response): static
    {
        if (headers_sent()) {
            throw new RuntimeException('Headers already sent');
            return $this;
        }

        foreach ($response->getHeaders() as $name => $values) {
            $this->sendHeader($name, $values);
        }

        header(
            sprintf(
                'HTTP/%s %d %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ),
            true,
            $response->getStatusCode()
        );

        return $this;
    }

    /**
     * Send a single header.
     *
     * @param string $name
     * @param array $values
     * @return static
     */
    protected function sendHeader(string $name, array $values): static
    {
        header(
            sprintf(
                '%s: %s',
                $name,
                implode(', ', $values)
            ),
            false
        );

        return $this;
    }

    /**
     * Send the response body.
     *
     * @param ResponseInterface $response
     * @return static
     */
    protected function sendBody(ResponseInterface $response): static
    {
        $body = $response->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }

        echo $body;

        return $this;
    }
}