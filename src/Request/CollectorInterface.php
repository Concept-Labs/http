<?php

namespace Concept\Http\Request\Collector;

use Psr\Http\Message\ServerRequestInterface;


/**
 * Interface CollectorInterface
 * @package Concept\Http\Request\Collector
 */
interface CollectorInterface
{

    /**
     * Collect the server request.
     *
     * @return ServerRequestInterface
     */
    public function collect(): ServerRequestInterface;
}