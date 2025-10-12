<?php
namespace Concept\Http\Router\Route\Handler\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Concept\Config\Config;
use Concept\Config\Contract\ConfigurableInterface;
use Concept\Singularity\Factory\ServiceFactory;

class MiddlewareFactory extends ServiceFactory
{
    public function create(array $args = []): MiddlewareInterface
    {
        $middleware = $this->createService(
            $args['preference'] ?? throw new \RuntimeException('Middleware preference not set'),
        );

        if (!$middleware instanceof MiddlewareInterface) {
            throw new \RuntimeException(sprintf(
                'Middleware service must implement MiddlewareInterface, %s given',
                is_object($middleware) ? get_class($middleware) : gettype($middleware)
            ));
        }

        $middleware instanceof ConfigurableInterface && isset($args['config'])
            && $middleware->setConfig(Config::fromArray($args['config']));

        return $middleware;
    }
}