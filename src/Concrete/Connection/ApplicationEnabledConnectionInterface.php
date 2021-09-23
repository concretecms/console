<?php

declare(strict_types=1);

namespace Concrete\Console\Concrete\Connection;

use Concrete\Core\Application\Application;

interface ApplicationEnabledConnectionInterface extends ConnectionInterface
{
    public function getApplication(): Application;
    public function connect(Application $app): void;
}
