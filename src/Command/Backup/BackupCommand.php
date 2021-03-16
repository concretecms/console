<?php
namespace Concrete\Console\Command\Backup;

use Concrete\Console\Command\AbstractInstallationCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BackupCommand extends AbstractInstallationCommand
{

    protected function configure()
    {
        $this
            ->setName('backup:backup')
            ->setDescription('Generates a Concrete installation backup.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

        $output->writeln('doing the backup');

    }


}