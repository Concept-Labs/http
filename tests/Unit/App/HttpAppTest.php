<?php

declare(strict_types=1);

use Concept\Http\App\HttpApp;
use Concept\Http\App\Config\AppConfig;
use Concept\Http\AppInterface;
use Concept\Http\RequestHandler\MiddlewareStackHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

describe('HttpApp', function () {
    
    beforeEach(function () {
        $this->config = new AppConfig([]);
        
        $this->serverRequestFactory = Mockery::mock(ServerRequestFactoryInterface::class);
        $this->responseFactory = Mockery::mock(ResponseFactoryInterface::class);
        $this->middlewareStackHandler = Mockery::mock(MiddlewareStackHandlerInterface::class);
        
        $this->app = new HttpApp(
            config: $this->config,
            serverRequestFactory: $this->serverRequestFactory,
            responseFactory: $this->responseFactory,
            middlewareStackHandlerPrototype: $this->middlewareStackHandler
        );
    });

    afterEach(function () {
        Mockery::close();
    });

    it('implements AppInterface', function () {
        expect($this->app)->toBeInstanceOf(AppInterface::class);
    });

    it('can add middleware', function () {
        $middleware = Mockery::mock(MiddlewareInterface::class);
        
        $result = $this->app->addMiddleware($middleware);
        
        expect($result)->toBeInstanceOf(AppInterface::class);
    });

    it('can add middleware with priority', function () {
        $middleware1 = Mockery::mock(MiddlewareInterface::class);
        $middleware2 = Mockery::mock(MiddlewareInterface::class);
        
        $this->app
            ->addMiddleware($middleware1, priority: 100)
            ->addMiddleware($middleware2, priority: 50);
        
        expect($this->app)->toBeInstanceOf(AppInterface::class);
    });

    it('can add middleware with id', function () {
        $middleware = Mockery::mock(MiddlewareInterface::class);
        
        $result = $this->app->addMiddleware($middleware, id: 'test-middleware');
        
        expect($result)->toBeInstanceOf(AppInterface::class);
    });

    it('can set server request', function () {
        $request = Mockery::mock(ServerRequestInterface::class);
        
        $result = $this->app->withServerRequest($request);
        
        expect($result)->toBeInstanceOf(AppInterface::class)
            ->and($result)->not->toBe($this->app); // Should return a clone
    });

    it('returns config', function () {
        $config = $this->app->getConfig();
        
        expect($config)->toBeInstanceOf(AppConfig::class);
    });
});
