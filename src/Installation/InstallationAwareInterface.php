<?php

declare(strict_types=1);

namespace Concrete\Console\Installation;

interface InstallationAwareInterface
{
    public function setInstallation(Installation $installation): void;
}
