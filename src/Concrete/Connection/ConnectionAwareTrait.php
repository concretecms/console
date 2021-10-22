<?php

declare(strict_types=1);

namespace Concrete\Console\Concrete\Connection;

trait ConnectionAwareTrait
{
    /**
     * @var ConnectionInterface
     */
    protected $traitConnection;

    /**
     * @see \Concrete\Console\Concrete\Connection\ConnectionAwareInterface::setConnection()
     */
    public function setConnection(ConnectionInterface $connection): void
    {
        $this->traitConnection = $connection;
    }

    protected function getConnection(): ?ConnectionInterface
    {
        return $this->traitConnection;
    }
}
