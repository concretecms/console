<?php

declare(strict_types=1);

namespace Concrete\Console\Command\Site;

use Concrete\Console\Application;
use Concrete\Console\Command\Command;
use Concrete\Console\Concrete\InstanceConfig;
use Concrete\Console\Util\Config;
use Concrete\Console\Util\Platform;
use League\Container\Container;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;

class SyncCommand extends Command
{

    public function __invoke(string $from, InputInterface $input)
    {
        $file = Config::findFile('concrete.json', $this->getInstallation());

        if (!$file) {
            throw new RuntimeException('Unable to locate concrete.json config file.');
        }

        $data = json_decode(file_get_contents($file), true);

        $instances = InstanceConfig::fromJson(dot_get($data, 'instances', []));
        $fromInstance = dot_get($instances, $from);

        if (!$fromInstance || !$fromInstance instanceof InstanceConfig) {
            throw new RuntimeException('No instances found.');
        }

        $this->output->outputStep('Backing up remote site');
        $process = $fromInstance->executeConsole('backup -q');
        $process->mustRun();
        $this->output->outputDone();

        $this->output->outputStep('Syncing remote file');
        $syncPath = Platform::tempDirectory(true) . '/backup.tar.gz';

        $path = trim($process->getOutput());
        $process = $fromInstance->downloadFile($path, $syncPath);
        $process->mustRun();
        $this->output->outputDone();

        $this->output->outputStep('Restoring');
        $result = $this->console->runCommand("backup:restore '{$syncPath}' --no-interaction", $this->output);
        $this->output->outputDone();

        return $result;
    }

    public static function register(Container $container, Application $console): void
    {
        $console->command('site:sync from [--config=]', self::class)
            ->descriptions(
                'Sync a remote site into this site using backups.',
                [
                    'from' => 'The location to sync from @remote:/path/to/backup.tar.gz or @remote',
                    '--config' => 'Specify the config file to use',
                ]
            );
    }
}
