<?php

namespace Concrete\Console\Concrete\Restore\Strategy;

use Concrete\Console\Concrete\Restore\Restoration;
use Concrete\Console\Util\Config;

class EnableMaintenanceMode extends AbstractOutputtingStrategy
{

    public function restore(Restoration $job): bool
    {
        $output = $this->getOutputStyle();
        $installation = $job->getInstallation();
        $installPath = $installation->getPath();
        $check = [
            '/index.php',
            '/public/index.php',
            '/web/index.php',
        ];

        $maintenance = Config::maintenancePage($installation);
        $cachePath = $job->tempDir('indexes');
        $found = false;

        foreach ($check as $item) {
            if (!file_exists($installPath . $item)) {
                continue;
            }

            $output->outputStep('Backing up and replacing ' . $item);

            // cache the file and replace it with our maintenance page
            $dirname = dirname($item);
            if ($dirname !== '/') {
                mkdir($cachePath . $dirname, 0777, true);
            }

            copy($installPath . $item, $cachePath . $item);
            if ($job->isDryRun()) {
                $output->outputDryrun();
            } else {
                file_put_contents($installPath . $item, $maintenance);
                $output->outputDone();
            }
            $found = true;
        }

        $reloadFpmCommand = $job->getAttributes()['reload-fpm-command'] ?? '';
        if ($reloadFpmCommand) {
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
        }

        $output->outputFinal();
        return $found;
    }
}
