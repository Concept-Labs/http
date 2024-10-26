<?php
namespace Concept\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ResponseMiddleware implements ResponseMiddlewareInterface
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        
        $this->flush($response);

        return $response;
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
     * @return self
     */
    protected function sendHeaders(ResponseInterface $response): self
    {
        if (headers_sent()) {
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
     * @return self
     */
    protected function sendHeader(string $name, array $values): self
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
     * @return self
     */
    protected function sendBody(ResponseInterface $response): self
    {
        $body = $response->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }

        echo $body;

        return $this;
    }
}