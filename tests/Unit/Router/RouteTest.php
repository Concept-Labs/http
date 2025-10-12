<?php

declare(strict_types=1);

use Concept\Http\Router\Route\Route;
use Concept\Http\Router\Route\RouteInterface;
use Concept\Http\Router\Route\Handler\RouteHandlerInterface;
use Concept\Config\Config;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

describe('Route', function () {
    
    beforeEach(function () {
        $this->routeHandler = Mockery::mock(RouteHandlerInterface::class);
        $this->route = new Route($this->routeHandler);
    });

    afterEach(function () {
        Mockery::close();
    });

    it('matches GET request on correct path', function () {
        $config = new Config([
            'path' => '/users',
            'method' => 'GET',
            'handler' => []
        ]);
        $this->route->setConfig($config);

        $request = Mockery::mock(ServerRequestInterface::class);
        $uri = Mockery::mock(UriInterface::class);
        
        $request->shouldReceive('getMethod')->andReturn('GET');
        $request->shouldReceive('getUri')->andReturn($uri);
        $uri->shouldReceive('getPath')->andReturn('/users');

        expect($this->route->match($request))->toBeTrue();
    });

    it('does not match when method differs', function () {
        $config = new Config([
            'path' => '/users',
            'method' => 'GET',
            'handler' => []
        ]);
        $this->route->setConfig($config);

        $request = Mockery::mock(ServerRequestInterface::class);
        $uri = Mockery::mock(UriInterface::class);
        
        $request->shouldReceive('getMethod')->andReturn('POST');
        $request->shouldReceive('getUri')->andReturn($uri);
        $uri->shouldReceive('getPath')->andReturn('/users');

        expect($this->route->match($request))->toBeFalse();
    });

    it('matches dynamic route parameters', function () {
        $config = new Config([
            'path' => '/users/{id}',
            'method' => 'GET',
            'handler' => []
        ]);
        $this->route->setConfig($config);

        $request = Mockery::mock(ServerRequestInterface::class);
        $uri = Mockery::mock(UriInterface::class);
        
        $request->shouldReceive('getMethod')->andReturn('GET');
        $request->shouldReceive('getUri')->andReturn($uri);
        $uri->shouldReceive('getPath')->andReturn('/users/123');

        expect($this->route->match($request))->toBeTrue();
    });

    it('supports multiple HTTP methods', function () {
        $config = new Config([
            'path' => '/users',
            'method' => ['GET', 'POST'],
            'handler' => []
        ]);
        $this->route->setConfig($config);

        $request = Mockery::mock(ServerRequestInterface::class);
        $uri = Mockery::mock(UriInterface::class);
        
        $request->shouldReceive('getUri')->andReturn($uri);
        $uri->shouldReceive('getPath')->andReturn('/users');

        // Test GET
        $request->shouldReceive('getMethod')->andReturn('GET');
        expect($this->route->match($request))->toBeTrue();

        // Test POST
        $request->shouldReceive('getMethod')->andReturn('POST');
        expect($this->route->match($request))->toBeTrue();
    });

    it('throws exception when path is not set', function () {
        $config = new Config([
            'method' => 'GET',
            'handler' => []
        ]);
        $this->route->setConfig($config);

        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getMethod')->andReturn('GET');

        expect(fn() => $this->route->match($request))
            ->toThrow(RuntimeException::class, 'Route path is not set');
    });

    it('throws exception when method is not set', function () {
        $config = new Config([
            'path' => '/users',
            'handler' => []
        ]);
        $this->route->setConfig($config);

        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getMethod')->andReturn('GET');
        
        expect(fn() => $this->route->match($request))
            ->toThrow(RuntimeException::class, 'Route methods are not set');
    });
});
