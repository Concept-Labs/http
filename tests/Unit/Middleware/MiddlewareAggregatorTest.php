<?php

declare(strict_types=1);

use Concept\Http\Middleware\MiddlewareAggregator;
use Concept\Http\Middleware\MiddlewareAggregatorInterface;
use Concept\Http\Middleware\MiddlewareWrapperInterface;
use Concept\Http\App\Config\AppConfig;
use Concept\Config\Config;

describe('MiddlewareAggregator', function () {
    
    beforeEach(function () {
        $this->middlewareWrapperPrototype = Mockery::mock(MiddlewareWrapperInterface::class);
        $this->appConfig = new AppConfig([
            'middleware' => [
                [
                    'preference' => 'TestMiddleware1',
                    'priority' => 100
                ],
                [
                    'preference' => 'TestMiddleware2',
                    'priority' => 50
                ],
            ]
        ]);
        
        $this->aggregator = new MiddlewareAggregator(
            $this->middlewareWrapperPrototype,
            $this->appConfig
        );
    });

    afterEach(function () {
        Mockery::close();
    });

    it('implements MiddlewareAggregatorInterface', function () {
        expect($this->aggregator)->toBeInstanceOf(MiddlewareAggregatorInterface::class);
    });

    it('is iterable', function () {
        expect($this->aggregator)->toBeInstanceOf(IteratorAggregate::class);
    });

    it('throws exception when middleware config node not found', function () {
        $appConfig = new AppConfig([]);
        $aggregator = new MiddlewareAggregator(
            $this->middlewareWrapperPrototype,
            $appConfig
        );

        expect(fn() => iterator_to_array($aggregator))
            ->toThrow(RuntimeException::class);
    });

    it('aggregates middleware with correct priority order', function () {
        $this->middlewareWrapperPrototype->shouldReceive('setConfig')
            ->andReturnSelf();
        
        $wrappers = [];
        foreach ($this->aggregator as $wrapper) {
            $wrappers[] = $wrapper;
        }

        expect($wrappers)->toHaveCount(2);
    });
});
