<?php
namespace Concept\Http\Message\Response;

use Psr\Http\Message\ResponseInterface;
use Concept\Http\Message\Message;

class Response extends Message implements ResponseInterface
{
    protected $statusCode;
    protected $reasonPhrase;

    public function __construct($statusCode = 200, $reasonPhrase = '')
    {
        $this->statusCode = $statusCode;
        $this->reasonPhrase = $reasonPhrase;
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * {@inheritDoc}
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $new = clone $this;
        $new->statusCode = $code;
        $new->reasonPhrase = $reasonPhrase;

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

}