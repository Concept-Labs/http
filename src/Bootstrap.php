<?php
namespace Concept\Http;

use Psr\Container\ContainerInterface;
use Concept\Singularity\Singularity;
use Concept\Singularity\Config\Plugin\ComposerPlugin;
use Concept\Config\ConfigInterface;
use Concept\Config\Factory as ConfigFactory;
use Concept\Composer\Composer;
use Concept\Config\Contract\ConfigurableInterface;
//use Concept\EventDispatcher\EventBusInterface;


class Bootstrap
{
    /**
     * The main configuration instance
     * 
     * @var ConfigInterface|null
     */
    protected ?ConfigInterface $config = null;

    /**
     * Bootstrap constructor.
     *
     * @param ContainerInterface $container
     * @param ConfigInterface $config
     */
    public function __construct(
        /**
         * Base path of the application. Must.
         */
        private string $base,

        /**
         * Source of the configuration may be a file path or glob pattern
         */
        private string $configSource,
        
        /**
         * Optional: use Singularity container if not set
         * Not tested on Separate PSR container if provided
         */
        private ?ContainerInterface $container = null  // use Singularity container if not set
    )
    {}

    /**
     * Get the base path
     * 
     * @return string
     */
    private function getBase(): string
    {
        return $this->base;
    }

    /**
     * Get the configuration source (file path or glob pattern)
     * 
     * @return string
     */
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
        if (!$this->config instanceof ConfigInterface) {
            /**
               basics
               @todo: improve context creation
             */
            $context = [
                //'APPID' => $this->getConfig()->get('app.id'),
                'BASE' => $this->getBase(),
                'VENDOR' => Composer::getVendorDir(),
            ];

            $this->config = (new ConfigFactory())
                ->withContext($context)
                ->withGlob($this->getBase() . DIRECTORY_SEPARATOR . $this->getConfigSrc())
                /**
                   @todo: register singularity (container) plugin separately
                 */
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
        /**
            ATTENTION: this is not tested with separate PSR container
            Prefer to use the provided container if set
            Otherwise, use Singularity container
         */
        $this->container ??= new Singularity($this->getConfig());

        /**
         * @important! Set the config to the container if it implements ConfigurableInterface
         */
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
                        $this->getConfig() //->node('app') //no separate node for app config anymore
                    )
                ->create();
    }

    /**
      @todo: EventBus as middleware
     */
    // protected function getEventBus(): ?EventBusInterface
    // {
    //     /**
    //       @todo EventBus as middleware: move to app
    //      */
    //     return $this->getContainer()?->get(EventBusInterface::class, [], [static::class]);
    // }

}