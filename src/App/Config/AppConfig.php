<?php

namespace Concept\Http\App\Config;

use Concept\Config\Config;
use Concept\Singularity\Contract\Lifecycle\SharedInterface;

class AppConfig extends Config implements AppConfigInterface, SharedInterface
{}