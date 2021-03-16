<?php
namespace Concrete\Console\Command\Backup;

use Concrete\Console\Command\AbstractInstallationCommand;
use Concrete\Core\File\StorageLocation\Configuration\LocalConfiguration;
use Concrete\Core\File\StorageLocation\StorageLocationFactory;
use Concrete\Core\Support\Facade\Facade;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputArgument;

class BackupCommand extends AbstractInstallationCommand
{

    protected function configure()
    {
        $this
            ->setName('backup:backup')
            ->setDescription('Generates a Concrete installation backup.')
            ->addArgument('file', InputArgument::OPTIONAL, 'Filename for the backup.')
            ->addOption('skip-core', null, InputOption::VALUE_NONE, 'Does not include the Concrete core in the archive.');
    }

    protected function exportDatabase(array &$manifest, string $directory, InputInterface $input, OutputInterface $output)
    {
        $dbOutput = $directory . DIRECTORY_SEPARATOR . 'db';
        mkdir($dbOutput);
        $dbOutput = $dbOutput . '/db.sql';

        $command = $this->getApplication()->find('database:dump');
        $arguments = ['file' => $dbOutput];
        $backupInput = new ArrayInput($arguments);
        $command->run($backupInput, $output);

        $manifest['contents']['database'] = true;;
    }

    protected function exportIndexEntrypoint(array &$manifest, string $directory, InputInterface $input, OutputInterface $output)
    {
        copy(DIR_BASE . DIRECTORY_SEPARATOR . DISPATCHER_FILENAME, $directory . DIRECTORY_SEPARATOR . DISPATCHER_FILENAME);
        $manifest['contents']['index'] = true;;
    }

    protected function exportFiles(array &$manifest, string $directory, InputInterface $input, OutputInterface $output)
    {
        $storageOutput = $directory . DIRECTORY_SEPARATOR . 'storage';
        mkdir($storageOutput);

        // loop through all storage locations
        $app = Facade::getFacadeApplication();
        $fileStorageFactory = $app->make(StorageLocationFactory::class);
        $storageLocations = $fileStorageFactory->fetchList();
        foreach($storageLocations as $storageLocation) {
            $configuration = $storageLocation->getConfigurationObject();
            if ($configuration instanceof LocalConfiguration) {
                $output->writeln(
                    sprintf("Adding files from storage location: '%s' (%s)",
                        $storageLocation->getName(),
                        $configuration->getRootPath()
                    )
                );

                $storageLocationOutput = $storageOutput . DIRECTORY_SEPARATOR . $storageLocation->getID();
                mkdir($storageLocationOutput);

                // Copy files from the storage location.
                $rsyncProcess = new Process([
                    'rsync',
                    '-av',
                    '-progress',
                    $configuration->getRootPath(),
                    $storageLocationOutput,
                    '--exclude=tmp/',
                    '--exclude=cache/',
                    '--exclude=incoming/'
                ]);
                $rsyncProcess->setTimeout(null);
                $rsyncProcess->run();

                if (!$rsyncProcess->isSuccessful()) {
                    throw \Exception($rsyncProcess->getErrorOutput());
                }

                $manifest['contents']['files'] = true;

            } else {
                $output->writeln(
                    sprintf("** <error>Alert! File storage location '%s' is not an instance of local configuration. It will not be included in this backup.</error>",
                    $storageLocation->getName()
                    )
                );
            }
        }
    }

    protected function writeManifest(array $manifest, string $directory)
    {
        file_put_contents($directory . DIRECTORY_SEPARATOR . 'manifest.json', json_encode($manifest));
    }

    protected function exportApplication(array &$manifest, string $directory, InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Exporting application/ directory...');
        $rsyncProcess = new Process([
            'rsync',
            '-av',
            '-progress',
            DIR_APPLICATION,
            $directory,
            '--exclude=config/doctrine/',
            '--exclude=files/',
        ]);
        $rsyncProcess->setTimeout(null);
        $rsyncProcess->run();

        if (!$rsyncProcess->isSuccessful()) {
            throw new \Exception($rsyncProcess->getErrorOutput());
        }

        $manifest['contents']['application'] = true;
        $manifest['contents']['applicationContents'] = [];
        foreach(['attributes', 'authentication', 'blocks', 'bootstrap', 'config', 'controllers', 'elements',
            'jobs', 'languages', 'mail', 'page_templates', 'single_pages', 'src', 'themes', 'tools', 'views'] as $item) {
            $manifest['contents']['applicationContents'][] = $item;
        }
    }

    protected function exportPackages(array &$manifest, string $directory, InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Exporting packages/ directory...');
        $rsyncProcess = new Process([
            'rsync',
            '-av',
            '-progress',
            DIR_PACKAGES,
            $directory
        ]);
        $rsyncProcess->setTimeout(null);
        $rsyncProcess->run();

        if (!$rsyncProcess->isSuccessful()) {
            throw new \Exception($rsyncProcess->getErrorOutput());
        }

        $manifest['contents']['packages'] = true;
    }

    protected function exportCore(array &$manifest, string $directory, InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('skip-core')) {
            $output->writeln('Exporting concrete/ directory...');
            $rsyncProcess = new Process(
                [
                    'rsync',
                    '-av',
                    '-progress',
                    DIR_BASE_CORE,
                    $directory
                ]
            );
            $rsyncProcess->setTimeout(null);
            $rsyncProcess->run();

            if (!$rsyncProcess->isSuccessful()) {
                throw new \Exception($rsyncProcess->getErrorOutput());
            }

            $manifest['contents']['core'] = true;
        } else {
            $manifest['contents']['core'] = false;
        }
    }

    protected function compressDirectory(string $directory, InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        if (!$file) {
            $app = Facade::getFacadeApplication();
            $siteName = $app->make('site')->getSite()->getSiteName();
            $date = new \DateTime();
            $file = sprintf('backup_%s_%s', snake_case($siteName), $date->format('Y-m-d-H-i-s'));
        }

        rename($directory, $file);

        $output->writeln(sprintf('Compressing directory: %s', $file));

        $tarProcess = new Process([
            'tar',
            '-zcf',
            $file . '.tar.gz',
            $file
        ]);
        $tarProcess->setTimeout(null);
        $tarProcess->run();

        if (!$tarProcess->isSuccessful()) {
            throw new \Exception($tarProcess->getErrorOutput());
        }

        $output->writeln('Removing temporary directory...');

        $rmProcess = new Process([
          'rm',
          '-r',
          $file
        ]);
        $rmProcess->setTimeout(null);
        $rmProcess->run();

        return 0;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $backupDirectory = getcwd() . DIRECTORY_SEPARATOR . sprintf('tmp_%s', uniqid());
        mkdir($backupDirectory);

        $app = Facade::getFacadeApplication();
        $site = $app->make('site')->getSite();
        $manifest = [
            'site' => $site->getSiteName(),
            'url' => $site->getSiteCanonicalURL(),
            'installationPath' => DIR_BASE,
            'version' => '1.0',
            'contents' => []
        ];

        $this->exportDatabase($manifest, $backupDirectory, $input, $output);
        $this->exportFiles($manifest, $backupDirectory, $input, $output);
        $this->exportApplication($manifest, $backupDirectory, $input, $output);
        $this->exportPackages($manifest, $backupDirectory, $input, $output);
        $this->exportCore($manifest, $backupDirectory, $input, $output);
        $this->exportIndexEntrypoint($manifest, $backupDirectory, $input, $output);
        $this->writeManifest($manifest, $backupDirectory);
        $this->compressDirectory($backupDirectory, $input, $output);

        $output->writeln('<fg=green>Backup complete!</>');

        return 0;
    }


}