<?php

declare(strict_types=1);

namespace Concrete\Console\Command;

use Concrete\Console\Application;
use Concrete\Console\Concrete\Connection\ApplicationEnabledConnectionInterface;
use Concrete\Console\Concrete\Connection\ConnectionAwareInterface;
use Concrete\Console\Concrete\Connection\ConnectionAwareTrait;
use Concrete\Console\Exception\Installation\VersionMismatch;
use Concrete\Console\Installation\InstallationAwareInterface;
use Concrete\Console\Installation\InstallationAwareTrait;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;

abstract class Command implements
    ContainerAwareInterface,
    OutputStyleAwareInterface,
    ConsoleAwareInterface,
    CommandGroupInterface,
    ConnectionAwareInterface,
    InstallationAwareInterface
{
    use ContainerAwareTrait;
    use ConnectionAwareTrait;
    use InstallationAwareTrait;

    /** @var OutputStyle */
    protected $output;

    /** @var Application */
    protected $console;

    /**
     * @param OutputStyle $outputStyle
     * @return void
     */
    public function setOutputStyle(OutputStyle $outputStyle): void
    {
        $this->output = $outputStyle;
    }

    public function setConsole(Application $application): void
    {
        $this->console = $application;
    }

    protected function getConsole(): Application
    {
        return $this->console;
    }

    public function getApplication(): \Concrete\Core\Application\Application
    {
        $connection = $this->getConnection();
        if (!$connection || !$connection instanceof ApplicationEnabledConnectionInterface) {
            throw new VersionMismatch('This command can only run on Application Enabled concrete installs.');
        }

        return $connection->getApplication();
    }
}
