<?php
namespace Concrete\Console\Concrete;

use Concrete\Console\Concrete\Connection\ConnectionInterface;

interface ClientInterface
{

    /**
     * Connect to a concrete5 site
     * @param string $path The path to the site to connect to
     * @return ConnectionInterface
     */
    public function connect($path): ConnectionInterface;

    /**
     * Attempt to disconnect
     * (This isn't going to be fully supported for awhile)
     *
     * @param ConnectionInterface $connection
     * @return bool
     */
    public function disconnect(ConnectionInterface $connection): bool;
}
