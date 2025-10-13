<?php
namespace Concept\Http;

use Psr\Container\ContainerInterface;
use Concept\Singularity\SingularityInterface;
use Concept\Singularity\Singularity;
use Concept\Http\App\Config\AppConfig;
use Concept\Http\App\Config\AppConfigInterface;
use Concept\Composer\Composer;
use Concept\Config\ConfigInterface;
use Concept\Config\Factory as ConfigFactory;
use Concept\Config\Contract\ConfigurableInterface;
use Concept\Singularity\Config\Plugin\ComposerPlugin;
use Concept\Config\Parser\Plugin\ConfigValuePlugin;
use Concept\Config\Parser\Plugin\ContextPlugin;
use Concept\Config\Parser\Plugin\Directive\CommentPlugin;
use Concept\Config\Parser\Plugin\Directive\ExtendsPlugin;
use Concept\Config\Parser\Plugin\Directive\ImportPlugin;
use Concept\Config\Parser\Plugin\Expression\EnvPlugin;
use Concept\Config\Parser\Plugin\Expression\ReferencePlugin;
use Concept\Config\Parser\Plugin\IncludePlugin;
//----
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

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
     * Loads configuration from files using the ConfigFactory with registered plugins.
     * Supports configuration caching in var/config.php for improved performance.
     * 
     * @return ConfigInterface
     */
    protected function getConfig(): ConfigInterface
    {
        if (!$this->config instanceof ConfigInterface) {
            // Check for cached configuration
            $cachedConfig = $this->getBase() . DIRECTORY_SEPARATOR . 'var/config.php';
            if (file_exists($cachedConfig)) {
                $cfg = require $cachedConfig;
                return $this->config = (new ConfigFactory(AppConfig::class))->fromArray($cfg);
            }

            // Build context for configuration parsing
            $context = [
                'BASE' => $this->getBase(),
                'VENDOR' => Composer::getVendorDir(),
            ];

            // Create configuration with plugins
            // Plugins are executed in priority order (higher = first)
            $this->config = (new ConfigFactory(AppConfig::class))
                ->withContext($context)
                ->withGlob($this->getBase() . DIRECTORY_SEPARATOR . $this->getConfigSrc())
                ->withPlugin(ComposerPlugin::class)       // Singularity DI integration
                ->withPlugin(EnvPlugin::class, 999)       // @env() directive for environment variables
                ->withPlugin(ContextPlugin::class, 998)   // ${VAR} syntax for context variables
                ->withPlugin(IncludePlugin::class, 997)   // @include directive
                ->withPlugin(ImportPlugin::class, 996)    // @import directive
                ->withPlugin(CommentPlugin::class, 996)   // Comment handling
                ->withPlugin(ReferencePlugin::class, 995) // @reference directive
                ->withPlugin(ConfigValuePlugin::class, 994) // Config value parsing
                ->withPlugin(ExtendsPlugin::class, 993)   // @extends directive (future feature)
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
        $this->container ??= new Singularity();

        /**
         * @important! Set the config to the container if it implements ConfigurableInterface
         */
        $this->container instanceof ConfigurableInterface
            && $this->container->setConfig($this->getConfig());
        
        $this->container instanceof SingularityInterface
            && $this->container->register(AppConfigInterface::class, $this->getConfig());

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
                ->create();
    }

}