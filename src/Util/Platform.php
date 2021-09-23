<?php

declare(strict_types=1);

namespace Concrete\Console\Util;

use RuntimeException;

use function defined;
use function function_exists;
use function strlen;

/**
 * Platform helper for uniform platform-specific tests.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class Platform
{
    /**
     * Parses tildes and environment variables in paths.
     *
     * @param string $path
     * @return string
     */
    public static function expandPath(string $path): string
    {
        if (preg_match('#^~[\\/]#', $path)) {
            return self::getUserDirectory() . substr($path, 1);
        }

        return preg_replace_callback(
            '#^(\$|(?P<percent>%))(?P<var>\w++)(?(percent)%)(?P<path>.*)#',
            function (array $matches) {
                // Treat HOME as an alias for USERPROFILE on Windows for legacy reasons
                if (Platform::isWindows() && $matches['var'] == 'HOME') {
                    return (getenv('HOME') ?: getenv('USERPROFILE')) . $matches['path'];
                }

                return getenv($matches['var']) . $matches['path'];
            },
            $path
        );
    }

    /**
     * @return string            The formal user home as detected from environment parameters
     * @throws RuntimeException If the user home could not reliably be determined
     */
    public static function getUserDirectory(): string
    {
        if (false !== ($home = getenv('HOME'))) {
            return $home;
        }

        if (self::isWindows() && false !== ($home = getenv('USERPROFILE'))) {
            return $home;
        }

        if (function_exists('posix_getuid') && function_exists('posix_getpwuid')) {
            $info = posix_getpwuid(posix_getuid());

            return $info['dir'];
        }

        throw new RuntimeException('Could not determine user directory');
    }

    public static function configDirectory(): string
    {
        $configDir = self::getUserDirectory() . '/.config/concrete';
        if (!file_exists($configDir)) {
            mkdir($configDir, 0777, true);
        }

        return $configDir;
    }

    public static function tempDirectory(bool $createSubpath = false): string
    {
        $tempDir = self::configDirectory() . '/tmp';
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        if ($createSubpath) {
            do {
                $subpath = $tempDir . '/' . uniqid('tmpdir_');
            } while (file_exists($subpath));

            mkdir($subpath, 0777, true);

            return $subpath;
        }

        return $tempDir;
    }

    /**
     * @return bool Whether the host machine is running a Windows OS
     */
    public static function isWindows()
    {
        return defined('PHP_WINDOWS_VERSION_BUILD');
    }

    /**
     * @param string $str
     * @return int    return a guaranteed binary length of the string, regardless of silly mbstring configs
     */
    public static function strlen(string $str): int
    {
        static $useMbString = null;
        if (null === $useMbString) {
            $useMbString = function_exists('mb_strlen') && ini_get('mbstring.func_overload');
        }

        if ($useMbString) {
            return mb_strlen($str, '8bit');
        }

        return strlen($str);
    }

    /**
     * @param ?resource $fd
     * @return bool
     */
    public static function isTty($fd = null): bool
    {
        if ($fd === null) {
            $fd = defined('STDOUT') ? STDOUT : fopen('php://stdout', 'w');
        }

        // modern cross-platform function, includes the fstat
        // fallback so if it is present we trust it
        if (function_exists('stream_isatty')) {
            return stream_isatty($fd);
        }

        // only trusting this if it is positive, otherwise prefer fstat fallback
        if (function_exists('posix_isatty') && posix_isatty($fd)) {
            return true;
        }

        $stat = @fstat($fd);
        // Check if formatted mode is S_IFCHR
        return $stat ? 0020000 === ($stat['mode'] & 0170000) : false;
    }
}
