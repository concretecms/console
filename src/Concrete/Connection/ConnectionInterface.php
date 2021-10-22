<?php

declare(strict_types=1);

namespace Concrete\Console\Concrete\Connection;

interface ConnectionInterface
{
    /**
     * Determine if this connection is connected
     */
    public function isConnected(): bool;

    /**
     * Disconnect a connection
     *
     * @return bool Success or failure
     */
    public function disconnect(): bool;
}
