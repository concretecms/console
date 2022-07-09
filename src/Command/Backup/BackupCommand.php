<?php

declare(strict_types=1);

namespace Concrete\Console\Command\Backup;

use Concrete\Console\Application;
use Concrete\Console\Command\Command;
use Concrete\Console\Installation\Manifest;
use Concrete\Console\Util\Platform;
use Concrete\Core\Entity\File\StorageLocation\StorageLocation;
use Concrete\Core\Entity\Site\Site;
use Concrete\Core\File\StorageLocation\Configuration\LocalConfiguration;
use Concrete\Core\File\StorageLocation\StorageLocationFactory;
use Concrete\Core\Package\PackageService;
use DateTime;
use Exception;
use League\Container\Container;
use Phar;
use PharData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Throwable;

class BackupCommand extends Command
{
    /**
     * @var bool
     */
    protected $skipCore;

    public function __invoke(?string $filename, InputInterface $input)
    {
        $this->skipCore = (bool)$input->getOption('skip-core');

        // Make a new directory to back up in
        $backupDirectory = Platform::tempDirectory(true);
        /** @var string $directory */
        $directory = rtrim($input->getOption('dir'), '/');

        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        $app = $this->getApplication();
        /** @var Site $site */
        $site = $app->make('site')->getSite();
        $manifest = (new Manifest())
            ->setHostName(gethostname())
            ->setVersion($this->getInstallation()->getVersion()->getVersion())
            ->setInstallationPath($this->getInstallation()->getPath())
            ->setSiteName($site->getSiteName())
            ->setUrl((string)$site->getSiteCanonicalURL());

        try {
            $manifest = $this->exportDatabase($manifest, $backupDirectory);
            $manifest = $this->exportFiles($manifest, $backupDirectory);
            $manifest = $this->exportApplication($manifest, $backupDirectory);
            $manifest = $this->exportPackages($manifest, $backupDirectory);
            $manifest = $this->exportCore($manifest, $backupDirectory);
            $manifest = $this->exportIndexEntrypoint($manifest, $backupDirectory);
            $this->writeManifest($manifest, $backupDirectory);

            // Finalize the backup
            $this->compressDirectory($backupDirectory, $directory, $filename);
        } catch (Throwable $e) {
            // Rethrow any errors
            throw $e;
        } finally {
            $this->output->writeln('Removing temporary directory...');

            $rmProcess = new Process(
                [
                    'rm',
                    '-r',
                    $backupDirectory
                ]
            );
            $rmProcess->setTimeout(null);
            $rmProcess->run();
        }

        $this->output->success('Backup complete!');
        return 0;
    }

    /**
     * @return void
     */
    public static function register(Container $container, Application $console): void
    {
        $console
            ->command(
                'backup:backup [filename] [--skip-core] [--temp] [--dir=]',
                self::class,
                ['backup']
            )
            ->descriptions(
                'Generate a Concrete installation backup',
                [
                    'filename' => 'Filename to use',
                    '--skip-core' => 'Does not include the Concrete core in the archive.',
                    '--temp' => 'Store relative to the concrete temp folder',
                    '--dir' => 'The directory to store the backup in'
                ]
            )->defaults(
                [
                    'dir' => Platform::configDirectory() . '/backups'
                ]
            );
    }

    protected function exportDatabase(Manifest $manifest, string $directory): Manifest
    {
        $dbOutput = $directory . DIRECTORY_SEPARATOR . 'db';
        mkdir($dbOutput);
        $dbOutput = $dbOutput . '/db.sql';

        $this->getConsole()->runCommand("database:dump '{$dbOutput}'");

        return $manifest->setDatabase('db/db.sql');
    }

    protected function exportIndexEntrypoint(Manifest $manifest, string $directory): Manifest
    {
        copy(
            DIR_BASE . DIRECTORY_SEPARATOR . DISPATCHER_FILENAME,
            $directory . DIRECTORY_SEPARATOR . DISPATCHER_FILENAME
        );

        return $manifest->setIncludeIndex(true);
    }

    protected function exportFiles(Manifest $manifest, string $directory): Manifest
    {
        $storageOutput = $directory . DIRECTORY_SEPARATOR . 'storage';
        mkdir($storageOutput);

        // loop through all storage locations
        $app = $this->getApplication();
        $fileStorageFactory = $app->make(StorageLocationFactory::class);
        $storageLocations = $fileStorageFactory->fetchList();
        /** @var StorageLocation $storageLocation */
        foreach ($storageLocations as $storageLocation) {
            $configuration = $storageLocation->getConfigurationObject();
            if ($configuration instanceof LocalConfiguration) {
                $this->output->writeln(
                    sprintf(
                        "Adding files from storage location: '%s' (%s)",
                        $storageLocation->getName(),
                        $configuration->getRootPath()
                    )
                );

                $storageLocationOutput = $storageOutput . DIRECTORY_SEPARATOR . $storageLocation->getID() . '/';
                mkdir($storageLocationOutput);

                // Copy files from the storage location.
                $rsyncProcess = new Process(
                    [
                        'rsync',
                        '-avL',
                        '--progress',
                        rtrim($configuration->getRootPath(), '/') . '/',
                        $storageLocationOutput,
                        '--exclude=tmp/',
                        '--exclude=cache/',
                        '--exclude=incoming/'
                    ]
                );
                $rsyncProcess->setTimeout(null);
                $rsyncProcess->run();

                if (!$rsyncProcess->isSuccessful()) {
                    throw new Exception($rsyncProcess->getErrorOutput());
                }

                $manifest = $manifest->addStorageLocation(
                    $storageLocation->getID(),
                    $storageLocation->getName(),
                    $storageLocation->isDefault(),
                    true
                );
            } else {
                $this->output->writeln(
                    sprintf(
                        "** <error>Alert! File storage location '%s' is not an instance of local configuration." .
                        "It will not be included in this backup.</error>",
                        $storageLocation->getName()
                    )
                );
            }
        }
        return $manifest;
    }

    protected function writeManifest(Manifest $manifest, string $directory): Manifest
    {
        file_put_contents($directory . DIRECTORY_SEPARATOR . 'manifest.json', json_encode($manifest));
        return $manifest;
    }

    protected function exportApplication(Manifest $manifest, string $directory): Manifest
    {
        $this->output->writeln('Exporting application/ directory...');
        $rsyncProcess = new Process(
            [
                'rsync',
                '-avL',
                '-progress',
                DIR_APPLICATION,
                $directory,
                '--exclude=config/doctrine/',
                '--exclude=config/*.database.php',
                '--exclude=config/database.php',
                '--exclude=files/',
            ]
        );
        $rsyncProcess->setTimeout(null);
        $rsyncProcess->run();

        if (!$rsyncProcess->isSuccessful()) {
            throw new Exception($rsyncProcess->getErrorOutput());
        }

        return $manifest->addApplicationItems(
            [
                'attributes',
                'authentication',
                'blocks',
                'bootstrap',
                'config',
                'controllers',
                'elements',
                'jobs',
                'languages',
                'mail',
                'page_templates',
                'single_pages',
                'src',
                'themes',
                'tools',
                'views'
            ]
        );
    }

    protected function exportPackages(Manifest $manifest, string $directory): Manifest
    {
        $this->output->writeln('Exporting packages/ directory...');
        /** @var PackageService $packages */
        $packages = $this->getApplication()->make(PackageService::class);
        $installed = $packages->getInstalledHandles();

        foreach ($packages->getAvailablePackages() as $package) {
            $handle = $package->getPackageHandle();
            $manifest = $manifest->addPackage(
                $handle,
                in_array($handle, $installed),
                file_exists($package->getPackagePath())
            );
        }

        // Make sure we have installed missing packages as well
        foreach ($installed as $packageHandle) {
            if ($manifest->getPackage($packageHandle)) {
                continue;
            }

            $manifest = $manifest->addPackage($packageHandle, true, false);
        }

        $rsyncProcess = new Process(
            [
                'rsync',
                '-avL',
                '-progress',
                DIR_PACKAGES,
                $directory
            ]
        );
        $rsyncProcess->setTimeout(null);
        $rsyncProcess->run();

        if (!$rsyncProcess->isSuccessful()) {
            throw new Exception($rsyncProcess->getErrorOutput());
        }

        return $manifest;
    }

    protected function exportCore(Manifest $manifest, string $directory): Manifest
    {
        if (!$this->skipCore) {
            $this->output->writeln('Exporting concrete/ directory...');
            $rsyncProcess = new Process(
                [
                    'rsync',
                    '-avL',
                    '-progress',
                    DIR_BASE_CORE,
                    $directory
                ]
            );
            $rsyncProcess->setTimeout(null);
            $rsyncProcess->run();

            if (!$rsyncProcess->isSuccessful()) {
                throw new Exception($rsyncProcess->getErrorOutput());
            }

            return $manifest->setIncludeCore(true);
        }

        return $manifest->setIncludeCore(false);
    }

    protected function compressDirectory(string $directory, string $outputDirectory, string $outputFile = null): void
    {
        $disablePhar = false;
        if (!in_array('phar', \stream_get_wrappers())) {
            $disablePhar = true;
            \stream_wrapper_restore('phar');
        }
        if (!$outputFile) {
            $app = $this->getApplication();
            $siteName = $app->make('site')->getSite()->getSiteName();
            $date = new DateTime();
            /** @psalm-suppress UndefinedFunction */
            $outputFile = sprintf('backup_%s_%s', snake_case($siteName), $date->format('Y-m-d-H-i-s'));
        }

        $this->output->writeln(sprintf('Compressing directory: %s', $outputFile));

        if (Phar::running() !== '') {
            $build = new PharData($outputDirectory . '/' . $outputFile . '.tar', 0);
            $build->buildFromDirectory($directory);
            $compressed = $build->compress(Phar::GZ);

            // Get rid of the uncompressed version
            unlink($build->getPath());

            $compressedFile = $compressed->getPath();
        } else {
            $tarProcess = new Process(
                [
                    'tar',
                    '-zcf',
                    $outputDirectory . '/' . $outputFile . '.tar.gz',
                    $directory
                ]
            );
            $tarProcess->setTimeout(null);
            $tarProcess->run();

            if (!$tarProcess->isSuccessful()) {
                throw new \Exception($tarProcess->getErrorOutput());
            }

            $compressedFile = $outputDirectory . '/' . $outputFile . '.tar.gz';
        }

        $this->output->writeln(['<fg=green>', 'Successfully created backup file at:<fg=cyan>']);
        $this->output->writeln($compressedFile, OutputInterface::VERBOSITY_QUIET);
        $this->output->writeln('</>');

        if ($disablePhar && in_array('phar', stream_get_wrappers(), true)) {
            stream_wrapper_unregister('phar');
        }
    }
}
