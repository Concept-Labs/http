<?php

declare(strict_types=1);

use Concept\Http\Bootstrap;
use Concept\Http\AppInterface;
use Psr\Container\ContainerInterface;
use Concept\Singularity\Singularity;

describe('Bootstrap', function () {
    
    beforeEach(function () {
        $this->basePath = sys_get_temp_dir() . '/concept-http-test-' . uniqid();
        mkdir($this->basePath, 0777, true);
        mkdir($this->basePath . '/config', 0777, true);
        
        // Create a minimal config file
        file_put_contents(
            $this->basePath . '/config/app.php',
            '<?php return ["app" => ["name" => "TestApp"]];'
        );
    });

    afterEach(function () {
        // Clean up
        if (is_dir($this->basePath)) {
            array_map('unlink', glob($this->basePath . '/config/*'));
            @rmdir($this->basePath . '/config');
            @rmdir($this->basePath);
        }
    });

    it('can be instantiated with required parameters', function () {
        $bootstrap = new Bootstrap(
            base: $this->basePath,
            configSource: 'config/*.php'
        );

        expect($bootstrap)->toBeInstanceOf(Bootstrap::class);
    });

    it('can be instantiated with custom container', function () {
        $container = new Singularity();
        
        $bootstrap = new Bootstrap(
            base: $this->basePath,
            configSource: 'config/*.php',
            container: $container
        );

        expect($bootstrap)->toBeInstanceOf(Bootstrap::class);
    });

    it('throws exception when base path does not exist', function () {
        $bootstrap = new Bootstrap(
            base: '/non/existent/path',
            configSource: 'config/*.php'
        );
        
        // The bootstrap will fail when trying to load config or create app
        expect(fn() => $bootstrap->app())->toThrow(Exception::class);
    });
});
