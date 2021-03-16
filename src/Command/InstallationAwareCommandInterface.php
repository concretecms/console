<?php
namespace Concrete\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

interface InstallationAwareCommandInterface
{

    public function getInstallation(InputInterface $input): ?string;

}