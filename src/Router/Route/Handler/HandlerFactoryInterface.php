<?php
namespace Concept\Http\Router\Route\Handler;

use Concept\Config\Contract\ConfigurableInterface;
use Concept\Singularity\Factory\ServiceFactoryInterface;

interface HandlerFactoryInterface extends ServiceFactoryInterface, ConfigurableInterface
{
}