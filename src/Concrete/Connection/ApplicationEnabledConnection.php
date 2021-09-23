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
     * Connect to modern concrete5
     * @param \Concrete\Core\Application\Application $application
     */
    public function connect(Application $application): void
    {
        $this->application = $application;
    }

    /**
     * Determine if this connection is connected
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->application !== null;
    }

    /**
     * Disconnect a connection
     *
     * @return bool Success or failure
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
     * @return Application
     */
    public function getApplication(): Application
    {
        if (!$this->application) {
            throw new \RuntimeException('Accessing the application before it has been populated.');
        }

        return $this->application;
    }
}
