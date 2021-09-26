<?php

declare(strict_types=1);

namespace Concrete\Console\Concrete\Connection;

use Concrete\Core\Application\Application;

class ApplicationEnabledConnection implements ApplicationEnabledConnectionInterface
{
    /**
     * @var Application|null
     */
    protected $application;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Console\Concrete\Connection\ApplicationEnabledConnectionInterface::connect()
     */
    public function connect(Application $application): void
    {
        $this->application = $application;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Console\Concrete\Connection\ConnectionInterface::isConnected()
     */
    public function isConnected(): bool
    {
        return $this->application !== null;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Console\Concrete\Connection\ConnectionInterface::disconnect()
     */
    public function disconnect(): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        $this->application = null;
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Console\Concrete\Connection\ApplicationEnabledConnectionInterface::getApplication()
     */
    public function getApplication(): Application
    {
        if (!$this->application) {
            throw new \RuntimeException('Accessing the application before it has been populated.');
        }

        return $this->application;
    }
}
