<?php

declare(strict_types=1);

namespace Concrete\Console\Concrete\Connection;

class LegacyConnection implements ConnectionInterface
{
    /**
     * Test if this connection is connected
     * @return bool
     */
    public function isConnected(): bool
    {
        return class_exists(\Concrete5_Model_Collection::class);
    }

    /**
     * Disconnect this connection
     * @return bool
     */
    public function disconnect(): bool
    {
        return !$this->isConnected();
    }
}
