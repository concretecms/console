<?php


namespace Concrete\Console\Installation;


trait InstallationAwareTrait
{

    /** @var Installation */
    protected $__installation;

    public function setInstallation(Installation $connection): void
    {
        $this->__installation = $connection;
    }

    protected function getInstallation(): Installation
    {
        return $this->__installation;
    }

}
