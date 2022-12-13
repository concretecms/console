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
     * {@inheritDoc}
     *
     * @see \Concrete\Console\Concrete\ClientInterface::disconnect()
     */
    public function disconnect(ConnectionInterface $connection): bool
    {
        return $connection->disconnect();
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Console\Concrete\ClientInterface::connect()
     */
    public function connect($path): ConnectionInterface
    {
        return $this->adapter->attach($path);
    }
}
