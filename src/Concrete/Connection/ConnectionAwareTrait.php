<?php
namespace Concrete\Console\Concrete\Connection;


trait ConnectionAwareTrait
{

    /** @var ConnectionInterface */
    protected $__connection;

    public function setConnection(ConnectionInterface $connection): void
    {
        $this->__connection = $connection;
    }

    protected function getConnection(): ?ConnectionInterface
    {
        return $this->__connection;
    }

}
