<?php

declare(strict_types=1);

namespace Concrete\Console\Command;

use Concrete\Console\Application;

interface ConsoleAwareInterface
{
    public function setConsole(Application $application): void;
}
