<?php

namespace Concrete\Console\Command;

use Concrete\Console\Application;
use League\Container\Container;

interface CommandGroupInterface
{

    public static function register(Container $container, Application $console): void;
}
