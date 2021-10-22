<?php

declare(strict_types=1);

namespace Concrete\Console\Concrete\Connection;

class LegacyConnection implements ConnectionInterface
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Console\Concrete\Connection\ConnectionInterface::isConnected()
     */
    public function isConnected(): bool
    {
        return class_exists(\Concrete5_Model_Collection::class);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Console\Concrete\Connection\ConnectionInterface::disconnect()
     */
    public function disconnect(): bool
    {
        return !$this->isConnected();
    }
}
