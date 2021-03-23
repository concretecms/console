<?php


namespace Concrete\Console\Concrete\Connection;


interface ConnectionAwareInterface
{

    public function setConnection(ConnectionInterface $connection): void;

}
