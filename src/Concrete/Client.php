<?php

declare(strict_types=1);

namespace Concrete\Console\Concrete;

use Concrete\Console\Concrete\Adapter\AdapterInterface;
use Concrete\Console\Concrete\Connection\ConnectionInterface;

class Client implements ClientInterface
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Attempt to disconnect
     * (This isn't going to be fully supported for awhile)
     *
     * @param ConnectionInterface $connection
     * @return bool
     */
    public function disconnect(ConnectionInterface $connection): bool
    {
        return $connection->disconnect();
    }

    /**
     * Get a connection to a concrete5 site
     * @param string $path The path to the site to connect to
     * @return ConnectionInterface
     */
    public function connect($path): ConnectionInterface
    {
        return $this->adapter->attach($path);
    }
}
