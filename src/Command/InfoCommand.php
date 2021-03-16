<?php
namespace Concrete\Console\Command;

use Concrete\Core\System\Info;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InfoCommand extends AbstractInstallationCommand
{

    protected function configure()
    {
        $this
            ->setName('info')
            ->setDescription('Get info about about the Concrete installation.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $info = new Info();
        $output->writeln('<info># Location</info>');
        $output->writeln(sprintf('Path to installation: %s', DIR_BASE));
        $output->writeln('<info># concrete5 Version</info>');
        $output->writeln('Installed - ' . ($info->isInstalled() ? 'Yes' : 'No'));
        $output->writeln($info->getCoreVersions());
    }


}