<?php

namespace Concrete\Console\Command\Site;

use Concrete\Console\Application;
use Concrete\Console\Command\Command;
use Concrete\Core\System\Info;
use League\Container\Container;

class InfoCommand extends Command
{

    public function __invoke()
    {
        $app = $this->getApplication();

        $info = $app->make(Info::class);
        $this->output->writeln('<info># Location</info>');
        $this->output->writeln(sprintf('Path to installation: %s', DIR_BASE));
        $this->output->writeln('<info># concrete5 Version</info>');
        $this->output->writeln('Installed - ' . ($info->isInstalled() ? 'Yes' : 'No'));
        $this->output->writeln($info->getCoreVersions());
    }

    /**
     * @param Container $container
     * @param Application $console
     * @return void
     */
    public static function register(Container $container, Application $console): void
    {
        $console->command('site:info', self::class, ['info'])
            ->descriptions('Get info about the current Concrete installation');
    }
}
