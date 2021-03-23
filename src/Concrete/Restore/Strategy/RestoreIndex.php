<?php


namespace Concrete\Console\Concrete\Restore\Strategy;


use Concrete\Console\Concrete\Restore\Restoration;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\Plugin\ListPaths;

/**
 * Copies out the backed up "index.php" file over the cached index.php files.
 * We copy over the cached versions because doing it live would override maintenance mode.
 */
class RestoreIndex extends AbstractOutputtingStrategy
{

    public function restore(Restoration $job): bool
    {
        $output = $this->getOutputStyle();
        $backup = $job->getBackup();

        $output->outputStep('Locating index');
        if (!$job->getManifest()->includesIndex()) {
            return false;
        }
        $output->outputDone();

        $output->outputStep('Reading from backup');
        $index = $backup->offsetGet('index.php');
        if (!$index || !($data = $index->getContent())) {
            return false;
        }
        $output->outputDone();

        $configDir = new Filesystem(new Local($job->tempDir()));
        $configDir->addPlugin(new ListPaths());

        $output->outputStep('Replacing cached indexes');
        foreach ($configDir->listPaths('indexes') as $file) {
            $configDir->put($file, $data);
        }
        $output->outputDone();

        $output->outputFinal();
        return true;
    }
}
