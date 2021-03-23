<?php


namespace Concrete\Console\Concrete\Restore\Strategy;

use Concrete\Console\Command\OutputStyleAwareTrait;
use Concrete\Console\Concrete\Restore\Restoration;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

abstract class AbstractDirectoryExtractStrategy extends AbstractOutputtingStrategy
{

    abstract protected function getExtractDirectory(): string;
    abstract protected function getExtractName(): string;
    abstract protected function shouldClear(Restoration $job): bool;

    public function restore(Restoration $job): bool
    {
        $output = $this->getOutputStyle();
        $path = $this->getExtractDirectory();
        $name = $this->getExtractName();

        $output->outputStep("Locating {$name} directory");

        // Locate the core we need to override
        $coreDirs = $job->findConcretePath($path);

        if (count($coreDirs) > 1) {
            throw new \InvalidArgumentException("Unable to find {$name} directory, multiple detected.");
        }

        if (!$coreDirs) {
            throw new \InvalidArgumentException("Unable to find {$name} directory, not detected.");
        }

        $file = array_shift($coreDirs);
        $dir = dirname($file);
        $output->outputDone($file);

        // Clear old directory if needed
        if ($this->shouldClear($job)) {
            $output->outputStep('Clearing old ' . $name);

            if (!$job->isDryRun()) {
                $fs = new Filesystem(new Local($dir));
                if (!$fs->deleteDir(basename($file))) {
                    return false;
                }
                $output->outputDone();
            } else {
                $output->outputDryrun();
            }
        }

        // Extract all files to the path
        $output->outputStep("Extracting {$name} directories... ");
        $subpathCount = mb_substr_count($path, '/', 'utf8');
        $extractPath = $dir;

        while ($subpathCount-- && $extractPath) {
            $extractPath = dirname($extractPath);
        }

        if (!$job->isDryRun()) {
            $job->getBackup()->extractTo($extractPath, [$path . '/'], true);
            $output->outputDone();
        } else {
            $output->outputDryrun();
        }

        // Output a string to make the success look nice
        $output->outputFinal();

        return true;
    }
}
