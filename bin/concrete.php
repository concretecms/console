<?php

require __DIR__ . '/../vendor/autoload.php';

use Concrete\Console\Application;

$application = new Application();
$application->addCommands(
    [
        new \Concrete\Console\Command\InfoCommand(),
        new \Concrete\Console\Command\Backup\BackupCommand(),
        new \Concrete\Console\Command\Database\DumpCommand(),

    ]
);
$application->run();
