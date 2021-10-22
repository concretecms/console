<?php

declare(strict_types=1);

namespace Concrete\Console\Command\Backup;

use Concrete\Console\Application;
use Concrete\Console\Command\Command;
use Concrete\Console\Concrete\Restore\Restoration;
use Concrete\Console\Concrete\Restore\RestorationManagerBuilder;
use Concrete\Console\Concrete\Restore\Strategy\Finalize;
use Concrete\Console\Exception\Installation\InstallationNotFound;
use Concrete\Console\Installation\ManifestFactory;
use Concrete\Console\Util\Platform;
use League\Container\Container;
use Symfony\Component\Console\Input\InputInterface;

class RestoreCommand extends Command
{

    public function __invoke(
        string $backupFile,
        RestorationManagerBuilder $restore,
        ManifestFactory $factory,
        InputInterface $input
    ): int {
        $disablePhar = false;
        if (!in_array('phar', \stream_get_wrappers())) {
            $disablePhar = true;
            \stream_wrapper_restore('phar');
        }

        $installation = $this->getInstallation();
        $manifest = $factory->forBackup($backupFile);

        if (!$this->output->confirm('Are you sure you want to restore this backup?')) {
            return 1;
        }

        if (!$installation) {
            throw new InstallationNotFound('No installation found.');
        }

        $restore
            ->enableMaintenancePage()
            ->restoreApplication(!!$input->getOption('skip-application'))
            ->restoreCore(!!$input->getOption('skip-core') || !$manifest->includesCore())
            ->restoreConfig(!!$input->getOption('skip-config'))
            ->restoreIndex(!!$input->getOption('skip-index') || !$manifest->includesIndex())
            ->restorePackages(!!$input->getOption('skip-packages') || !$manifest->getPackages())
            ->restoreDatabase(!!$input->getOption('skip-database') || !$manifest->getDatabase())
            ->restoreFiles(!!$input->getOption('skip-files'))
            ->finalize();

        // Handle various skips
        if ($input->getOption('skip-application')) {
            $restore->restoreApplication(true);
        }

        if ($input->getOption('skip-core')) {
            $restore->restoreCore(true);
        }

        $job = Restoration::forBackup(
            new \PharData($backupFile),
            $manifest,
            $installation,
            Platform::tempDirectory(true),
            !!$input->getOption('dryrun'),
            ['reload-fpm-command' => $input->getOption('reload-fpm-command')]
        );

        try {
            $restore->resolve()->restore($job);
        } finally {
            $this->output->newLine();
            $this->output->writeln('Cleaning up...');
            $finalize = new Finalize();
            $finalize->clean($job);

            if ($disablePhar && in_array('phar', stream_get_wrappers(), true)) {
                stream_wrapper_unregister('phar');
            }
        }

        return 0;
    }

    public static function register(Container $container, Application $console): void
    {
        $console
            ->command(
                'backup:restore backupFile [-D|--dryrun] 
                        [--skip-db] [--skip-core] [--skip-packages] [--skip-config] [--skip-files] [--skip-application] 
                        [--skip-index] [--skip-database] [--reload-fpm-command=]',
                self::class,
                ['restore']
            )
            ->descriptions(
                'Restore a concrete5 site from a backup.',
                [
                    'backupFile' => 'The file to restore from',
                    '--dryrun' => 'Don\'t actually run restoration',
                    '--skip-core' => 'Skip the concrete5 core',
                    '--skip-packages' => 'Skip packages',
                    '--skip-config' => 'Skip config',
                    '--skip-files' => 'Skip any storage locations',
                    '--skip-application' => 'Skip restoring the full application directory',
                    '--skip-index' => 'Skip the index.php file',
                    '--skip-database' => 'Skip restoring the database',
                    '--reload-fpm-command' => 'Command to run to reload FPM if needed',
                ]
            );
    }
}
