<?php

declare(strict_types=1);

use Concept\Http\Middleware\MiddlewareWrapper;
use Concept\Http\Middleware\MiddlewareWrapperInterface;
use Concept\Singularity\Factory\FactoryInterface;
use Concept\Config\Config;
use Concept\Singularity\Config\ConfigNodeInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

describe('MiddlewareWrapper', function () {
    
    beforeEach(function () {
        $this->factory = Mockery::mock(FactoryInterface::class);
        $this->wrapper = new MiddlewareWrapper($this->factory);
    });

    afterEach(function () {
        Mockery::close();
    });

    it('implements MiddlewareWrapperInterface', function () {
        expect($this->wrapper)->toBeInstanceOf(MiddlewareWrapperInterface::class);
    });

    it('implements MiddlewareInterface', function () {
        expect($this->wrapper)->toBeInstanceOf(MiddlewareInterface::class);
    });

    it('creates prototype when cloned', function () {
        $config = new Config([
            ConfigNodeInterface::NODE_PREFERENCE => 'TestMiddleware'
        ]);
        $this->wrapper->setConfig($config);

        $clone = $this->wrapper->prototype();

        expect($clone)->toBeInstanceOf(MiddlewareWrapper::class)
            ->and($clone)->not->toBe($this->wrapper);
    });

    it('throws exception when preference not set', function () {
        $config = new Config([]);
        $this->wrapper->setConfig($config);

        $request = Mockery::mock(ServerRequestInterface::class);
        $handler = Mockery::mock(RequestHandlerInterface::class);

        expect(fn() => $this->wrapper->process($request, $handler))
            ->toThrow(RuntimeException::class, 'No preference set for middleware');
    });
});
