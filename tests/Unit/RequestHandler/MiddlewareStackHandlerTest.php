<?php

declare(strict_types=1);

use Concept\Http\RequestHandler\MiddlewareStackHandler;
use Concept\Http\RequestHandler\MiddlewareStackHandlerInterface;
use Concept\Http\RequestHandler\MiddlewareRequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

describe('MiddlewareStackHandler', function () {
    
    beforeEach(function () {
        $this->middlewareRequestHandler = Mockery::mock(MiddlewareRequestHandlerInterface::class);
        $this->stackHandler = new MiddlewareStackHandler($this->middlewareRequestHandler);
    });

    afterEach(function () {
        Mockery::close();
    });

    it('implements MiddlewareStackHandlerInterface', function () {
        expect($this->stackHandler)->toBeInstanceOf(MiddlewareStackHandlerInterface::class);
    });

    it('implements RequestHandlerInterface', function () {
        expect($this->stackHandler)->toBeInstanceOf(RequestHandlerInterface::class);
    });

    it('can set final handler', function () {
        $finalHandler = Mockery::mock(RequestHandlerInterface::class);
        
        $result = $this->stackHandler->withFinalHandler($finalHandler);
        
        expect($result)->toBeInstanceOf(MiddlewareStackHandlerInterface::class)
            ->and($result)->not->toBe($this->stackHandler); // Should return clone
    });

    it('can add middleware', function () {
        $middleware = Mockery::mock(MiddlewareInterface::class);
        
        $result = $this->stackHandler->addMiddleware($middleware);
        
        expect($result)->toBeInstanceOf(MiddlewareStackHandlerInterface::class);
    });

    it('throws exception when handling without final handler', function () {
        $request = Mockery::mock(ServerRequestInterface::class);
        
        expect(fn() => $this->stackHandler->handle($request))
            ->toThrow(RuntimeException::class, 'No final handler configured');
    });
});
