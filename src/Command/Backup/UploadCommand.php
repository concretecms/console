<?php


namespace Concrete\Console\Command\Backup;


use Concrete\Console\Command\Command;

class UploadCommand extends Command
{

    public function __invoke(
        string $backupFile,
        RestorationManagerBuilder $restore,
        ManifestFactory $factory,
        InputInterface $input
    ) {

}
