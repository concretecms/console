<?php

declare(strict_types=1);

namespace Concrete\Console\Installation;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

interface InstallationAwareInterface
{

    public function setInstallation(Installation $installation): void;
}
