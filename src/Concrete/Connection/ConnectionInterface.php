<?php


namespace Concrete\Console\Concrete\Connection;


interface ConnectionInterface
{

    /**
     * Determine if this connection is connected
     * @return bool
     */
    public function isConnected(): bool;

    /**
     * Disconnect a connection
     * @return bool Success or failure
     */
    public function disconnect(): bool;
}
