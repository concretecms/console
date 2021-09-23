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

    /**
     * @param string $path
     * @return string
     */
    public static function getConcretePath(string $path)
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

    /**
     * @param string $path
     * @return string
     */
    public static function getApplicationPath(string $path)
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

    /**
     * @param array $check
     * @param string $cacheKey
     * @param string $path
     * @return string
     */
    protected static function locatePath(array $check, string $cacheKey, string $path)
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
