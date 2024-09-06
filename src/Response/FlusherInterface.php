<?php
namespace Concept\Http\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface FlusherInterface
 * @package Concept\Http\Response
 */
interface FlusherInterface
{
    /**
     * Flush the HTTP response.
     *
     * @param ResponseInterface $response
     * @return void
     */
    public function flush(ResponseInterface $response): void
}