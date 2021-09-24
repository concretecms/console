<?php

declare(strict_types=1);

namespace Concrete\Console\Command;

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
    use OutputStyleAwareTrait;
    use ConsoleAwareTrait;
    use ConnectionAwareTrait;
    use InstallationAwareTrait;

    /**
     * @throws \Concrete\Console\Exception\Installation\VersionMismatch
     */
    public function getApplication(): \Concrete\Core\Application\Application
    {
        $connection = $this->getConnection();
        if (!$connection || !$connection instanceof ApplicationEnabledConnectionInterface) {
            throw new VersionMismatch('This command can only run on Application Enabled concrete installs.');
        }

        return $connection->getApplication();
    }
}
