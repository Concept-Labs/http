<?php
namespace Concept\Http;

use Psr\Container\ContainerInterface;
use Concept\Composer\Composer;
use Concept\Config\Factory as ConfigFactory;
use Concept\Config\ConfigInterface;
use Concept\Config\Contract\ConfigurableInterface;
use Concept\Singularity\Singularity;
use Concept\Singularity\Config\Plugin\ComposerPlugin;
//use Concept\EventDispatcher\EventBusInterface;


class Bootstrap
{

    private ?ConfigInterface $config = null;

    /**
     * Bootstrap constructor.
     *
     * @param ContainerInterface $container
     * @param ConfigInterface $config
     */
    public function __construct(
        private string $base,
        private string $configSource,
        private ?ContainerInterface $container = null  // use Singularity container if not set
    )
    {
        $this
            ->initErrorHandler()
        //    ->initEventBus()
        ;
    }

    private function getBase(): string
    {
        return $this->base;
    }

    private function getConfigSrc(): string
    {
        return $this->configSource;
    }

    /**
     * Get the configuration
     * 
     * @return ConfigInterface
     */
    protected function getConfig(): ConfigInterface
    {
        if (null === $this->config) {

            $context = [
                //'APPID' => $this->getConfig()->get('app.id'),
                'BASE' => $this->getBase(),
                'VENDOR' => Composer::getVendorDir(),
            ];

            $this->config = (new ConfigFactory())
                ->withContext($context)
                ->withGlob($this->getBase() . DIRECTORY_SEPARATOR . $this->getConfigSrc())
                ->withPlugin(ComposerPlugin::class) // register the Singularity Composer plugin
                ->create()
            ;
        }

        return $this->config;
    }

    /**
     * Get the container
     * 
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        $this->container ??= new Singularity($this->getConfig()); // prefer to use Singularity container if not set

        $this->container instanceof ConfigurableInterface
            && $this->container->setConfig($this->getConfig());

        return $this->container;
    }
   
    

    /**
     * Create the application instance
     *
     * @return AppInterface
     */
    public function app(): AppInterface
    {
        return $this
            ->getContainer()
                ->get(AppFactoryInterface::class)
                    ->setConfig(
                        $this->getConfig()->node('app')
                    )
                ->create();
    }

    /**
     * Get the application ID
     * 
     * @return string
     */
    protected function getAppId(): string
    {
        /**
         @todo
         */
        return uniqid('app_', true);
        //return $this->config->get('app.id');
    }

    

    // protected function getEventBus(): ?EventBusInterface
    // {
    //     /**
    //       @todo EventBus as middleware: move to app
    //      */
    //     return $this->getContainer()?->get(EventBusInterface::class, [], [get_class($this)]);
    // }

    protected function initErrorHandler(): static
    {
        // set_error_handler(fn($errno, $errstr, $errfile, $errline) => throw new \ErrorException($errstr, 0, $errno, $errfile, $errline));
        return $this;
    }
}