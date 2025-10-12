<?php

declare(strict_types=1);

use Concept\Http\Router\Router;
use Concept\Http\Router\RouterInterface;
use Concept\Http\Router\Route\RouteAggregatorInterface;
use Concept\Http\Router\Route\RouteInterface;
use Concept\Config\Config;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

describe('Router', function () {
    
    beforeEach(function () {
        $this->routeAggregator = Mockery::mock(RouteAggregatorInterface::class);
        $this->router = new Router($this->routeAggregator);
    });

    afterEach(function () {
        Mockery::close();
    });

    it('implements RouterInterface', function () {
        expect($this->router)->toBeInstanceOf(RouterInterface::class);
    });

    it('can process request when route matches', function () {
        $request = Mockery::mock(ServerRequestInterface::class);
        $handler = Mockery::mock(RequestHandlerInterface::class);
        $response = Mockery::mock(ResponseInterface::class);
        $route = Mockery::mock(RouteInterface::class);

        $uri = Mockery::mock(UriInterface::class);
        $uri->shouldReceive('getPath')->andReturn('/test');
        $request->shouldReceive('getUri')->andReturn($uri);
        $request->shouldReceive('getMethod')->andReturn('GET');

        $route->shouldReceive('match')->with($request)->andReturn(true);
        $route->shouldReceive('handle')->with($request)->andReturn($response);

        $this->routeAggregator->shouldReceive('hasConfig')->andReturn(true);
        $this->routeAggregator->shouldReceive('getIterator')->andReturn(new ArrayIterator([$route]));

        $config = new Config(['routes' => []]);
        $this->router->setConfig($config);

        $result = $this->router->process($request, $handler);

        expect($result)->toBe($response);
    });

    it('delegates to handler when no route matches', function () {
        $request = Mockery::mock(ServerRequestInterface::class);
        $handler = Mockery::mock(RequestHandlerInterface::class);
        $response = Mockery::mock(ResponseInterface::class);

        $this->routeAggregator->shouldReceive('hasConfig')->andReturn(true);
        $this->routeAggregator->shouldReceive('getIterator')->andReturn(new ArrayIterator([]));

        $handler->shouldReceive('handle')->with($request)->andReturn($response);

        $config = new Config(['routes' => []]);
        $this->router->setConfig($config);

        $result = $this->router->process($request, $handler);

        expect($result)->toBe($response);
    });
});
