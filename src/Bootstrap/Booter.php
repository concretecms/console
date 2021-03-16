<?php

namespace Concrete\Console\Bootstrap;

use Concrete\Console\Installation\Installation;

class Booter
{

    public function boot(Installation $installation)
    {
        define('DIR_CONFIG_SITE', $installation->getPath() . '/application/config');
        define('DIR_BASE', $installation->getPath());

        // Include configuration
        require $installation->getPath() . '/concrete/bootstrap/configure.php';

        // Include autoloaders
        require $installation->getPath() . '/concrete/bootstrap/autoload.php';

        // Begin startup
        $app = require $installation->getPath() . '/concrete/bootstrap/start.php';
    }
}