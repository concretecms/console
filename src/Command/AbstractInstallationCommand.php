<?php
namespace Concrete\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractInstallationCommand extends Command implements InstallationAwareCommandInterface
{

    public function __construct(string $name = null)
    {
        parent::__construct($name);
        $this->addInstallationOption();
    }

    protected function addInstallationOption()
    {
        $this->addOption('installation', 'i', InputOption::VALUE_OPTIONAL, 'Concrete installation to run command against.');
    }

    public function getInstallation(InputInterface $input): ?string
    {
        return $input->hasOption('installation') ? $input->getOption('installation') : null;
    }
}