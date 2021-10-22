<?php

declare(strict_types=1);

namespace Concrete\Console\Installation;

trait InstallationAwareTrait
{
    /** @var Installation */
    protected $traitInstallation;

    /**
     * @see \Concrete\Console\Installation\InstallationAwareInterface::setInstallation()
     */
    public function setInstallation(Installation $connection): void
    {
        $this->traitInstallation = $connection;
    }

    protected function getInstallation(): Installation
    {
        return $this->traitInstallation;
    }
}
