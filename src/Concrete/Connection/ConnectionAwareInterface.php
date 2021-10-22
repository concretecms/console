<?php

declare(strict_types=1);

namespace Concrete\Console\Concrete\Connection;

interface ConnectionAwareInterface
{
    public function setConnection(ConnectionInterface $connection): void;
}
