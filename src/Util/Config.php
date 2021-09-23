<?php

declare(strict_types=1);

namespace Concrete\Console\Util;

use Concrete\Console\Installation\Installation;

class Config
{
    public static function findFile(string $fileName, Installation $installation = null): ?string
    {
        $fileName = trim($fileName, '/');

        $check = [];
        if ($installation) {
            $check[] = $installation->getPath() . '/.concrete/' . $fileName;
        }

        $check[] = Platform::configDirectory() . '/' . $fileName;
        $check[] = __DIR__ . '/../../.concrete/' . $fileName;

        foreach ($check as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    public static function maintenancePage(Installation $intallation = null): string
    {
        $path = self::findFile('index.maintenance.php', $intallation);
        if ($path) {
            return file_get_contents($path);
        }

        return 'MAINTENANCE MODE';
    }
}
