<?php

declare(strict_types=1);

namespace Concrete\Console\Util;

use Concrete\Console\Exception\Installation\InstallationNotFound;

class Installation
{
    protected static $pathCache = [
        'core' => [],
        'application' => [],
    ];

    public static function getConcretePath(string $path): string
    {
        return self::locatePath(
            [
                'concrete',
                'public/concrete',
                'web/concrete',
            ],
            'application',
            $path
        );
    }

    public static function getApplicationPath(string $path): string
    {
        return self::locatePath(
            [
                'application',
                'public/application',
                'web/application',
            ],
            'application',
            $path
        );
    }

    protected static function locatePath(array $check, string $cacheKey, string $path): string
    {
        if (isset(self::$pathCache[$cacheKey][$path])) {
            return self::$pathCache[$cacheKey][$path];
        }

        foreach ($check as $dir) {
            $file = rtrim($path, '/') . '/' . $dir;

            if (file_exists($file)) {
                return $file;
            }
        }

        throw InstallationNotFound::atPath($path);
    }
}
