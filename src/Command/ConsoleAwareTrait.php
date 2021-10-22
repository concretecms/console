<?php

declare(strict_types=1);

namespace Concrete\Console\Command;

use Concrete\Console\Application;

trait ConsoleAwareTrait
{
    /**
     * @var Application
     */
    protected $console;

    /**
     * @see \Concrete\Console\Command\ConsoleAwareInterface::setConsole()
     */
    public function setConsole(Application $application): void
    {
        $this->console = $application;
    }

    protected function getConsole(): Application
    {
        return $this->console;
    }
}
