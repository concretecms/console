<?php

declare(strict_types=1);

namespace Concrete\Console\Concrete\Restore\Strategy;

use Concrete\Console\Concrete\Restore\Restoration;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

class Finalize extends AbstractOutputtingStrategy
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Console\Concrete\Restore\StrategyInterface::restore()
     */
    public function restore(Restoration $job): bool
    {
        $cliPath = $job->findConcretePath('concrete/bin/concrete5');
        if (!$cliPath) {
            throw new \RuntimeException('Unable to locate concrete5 cli tool.');
        } else {
            $cliPath = array_shift($cliPath);
        }

        return $this->generateProxies($job, $cliPath)
            && $this->clearCache($job, $cliPath)
            && $this->restoreIndexes($job)
            && $this->sync($job)
            && $this->reloadFpm($job);
    }

    public function clean(Restoration $job): bool
    {
        return $this->clearTemp($job);
    }

    private function restoreIndexes(Restoration $job): bool
    {
        $output = $this->getOutputStyle();
        $install = $job->getInstallation();
        $installPath = $install->getPath();
        $basePath = $job->tempDir('indexes');
        $allFiles = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $basePath,
                \RecursiveDirectoryIterator::SKIP_DOTS
            )
        );
        $dry = $job->isDryRun();

        /** @var \SplFileInfo $file */
        foreach ($allFiles as $file) {
            $output->outputStep('Restoring index file ' . $file->getPathname());

            $newPath = $installPath . '/' . trim(substr($file->getPathname(), strlen($basePath)), '/');
            if (!$dry) {
                copy($file->getPathname(), $newPath);
                $output->outputDone();
            } else {
                $output->outputDryrun();
            }
        }

        return true;
    }

    private function sync(Restoration $job): bool
    {
        $job->tempDir('sync');
        /*
         * $sync = $job->tempDir('sync');
         * $sync = new Rsync();
         * var_dump($sync->getCommand($sync, $job->getInstallation()));
         */

        return true;
    }

    private function clearTemp(Restoration $job): bool
    {
        $filesystem = new Filesystem(new Local('/'));
        return $filesystem->deleteDir($job->tempDir());
    }

    private function generateProxies(Restoration $job, string $path): bool
    {
        $output = $this->getOutputStyle();
        $output->outputStep('Regenerating database proxies');
        if ($job->isDryRun()) {
            $output->outputDryrun();
            return true;
        }

        $command = process($path . ' orm:generate:proxies');
        $command->mustRun();

        if ($command->isSuccessful()) {
            $output->outputDone();
            return true;
        }

        $output->outputDone('Failed', '<fg=red>');
        return false;
    }

    private function clearCache(Restoration $job, string $path): bool
    {
        $output = $this->getOutputStyle();
        $output->outputStep('Clearing cache');

        if ($job->isDryRun()) {
            $output->outputDryrun();
            return true;
        }

        $command = process($path . ' c5:clear-cache');
        $command->mustRun();

        if ($command->isSuccessful()) {
            $output->outputDone();
            return true;
        }

        $output->outputDone('Failed', '<fg=red>');
        return false;
    }

    private function reloadFpm(Restoration $job): bool
    {
        $reloadFpmCommand = $job->getAttributes()['reload-fpm-command'] ?? '';
        if ($reloadFpmCommand) {
            $output = $this->getOutputStyle();
            $output->outputStep('Reloading PHP-FPM');

            if ($job->isDryRun()) {
                $output->outputDryrun();
            } else {
                $result = process($reloadFpmCommand)->mustRun();
                if ($result->isSuccessful()) {
                    $output->outputDone();
                } else {
                    return false;
                }
            }

            $output->outputFinal();
        }

        return true;
    }
}
