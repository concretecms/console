<?php

declare(strict_types=1);

namespace Concrete\Console\Concrete\Restore\Strategy;

use Concrete\Console\Concrete\Connection\ApplicationEnabledConnectionInterface;
use Concrete\Console\Concrete\Connection\ConnectionInterface;
use Concrete\Console\Concrete\Restore\Restoration;
use Concrete\Core\File\StorageLocation\StorageLocationFactory;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use League\Flysystem\Adapter\Local;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;

class RestoreStorageLocations extends AbstractOutputtingStrategy implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Console\Concrete\Restore\StrategyInterface::restore()
     */
    public function restore(Restoration $job): bool
    {
        $output = $this->getOutputStyle();
        $output->outputStep('Validating site');
        $install = $this->container->get(ConnectionInterface::class);
        if (!$install instanceof ApplicationEnabledConnectionInterface) {
            return false;
        }

        $app = $install->getApplication();
        /** @var StorageLocationFactory $fileStorageFactory */
        $fileStorageFactory = $app->make(StorageLocationFactory::class);

        // Extract storage locations to a temp dir
        $tmp = $job->tempDir('storage');
        $job->getBackup()->extractTo($tmp, 'storage/');

        $output->outputDone();

        foreach ($job->getManifest()->getStorageLocations() as $location) {
            $output->outputStep('Extracting storage location ' . $location['name']);

            if ($job->isDryRun()) {
                $output->outputDryrun();
                continue;
            }

            $backupStorage = new Filesystem(new Local($tmp . '/storage/' . $location['id']));
            $locationObject = $fileStorageFactory->fetchByID($location['id']);

            /** @psalm-suppress DeprecatedClass Mountmanager is not removed in v2 */
            $manager = new MountManager([
                'bu' => $backupStorage,
                'sl' => $locationObject->getFileSystemObject()
            ]);

            foreach ($manager->listContents('bu://', true) as $file) {
                $path = $file['path'];
                if ($file['type'] === 'dir') {
                    $manager->createDir('sl://' . $path);
                    continue;
                }

                try {
                    $manager->delete('sl://' . $path);
                } catch (FileNotFoundException $e) {
                    // Ignore
                }
                $manager->copy('bu:///' . $path, 'sl://' . $path);
            }
        }

        $this->getOutputStyle()->outputFinal();
        return true;
    }
}
