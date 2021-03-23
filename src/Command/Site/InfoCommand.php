<?php
namespace Concrete\Console\Command\Site;

use Concrete\Console\Application;
use Concrete\Console\Command\Command;
use Concrete\Console\Concrete\Connection\ApplicationEnabledConnection;
use Concrete\Core\System\Info;
use League\Container\Container;

class InfoCommand extends Command
{

    public function __invoke()
    {
        $app = $this->getApplication();

        $info = $app->make(Info::class);
        $this->writeln('<info># Location</info>');
        $this->writeln(sprintf('Path to installation: %s', DIR_BASE));
        $this->writeln('<info># concrete5 Version</info>');
        $this->writeln('Installed - ' . ($info->isInstalled() ? 'Yes' : 'No'));
        $this->writeln($info->getCoreVersions());
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
