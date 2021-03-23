<?php

namespace Concrete\Console\Installation;

use Concrete\Console\Exception\Installation\InstallationNotFound;
use Concrete\Console\Installation\Detector\DetectorInterface;
use Concrete\Console\Installation\Detector\BaseDetector;
use Concrete\Console\Installation\Detector\Version6Detector;

class InstallationDetector
{

    protected const DIRNAME_COMPOSER_PUBLIC = 'public';
    protected const DIRECTORY_SEPARATOR = '/';

    /**
     * @var DetectorInterface
     */
    protected $versionDetector;

    public function __construct(DetectorInterface $detector)
    {
        $this->versionDetector = $detector;
    }

    /**
     * Deals with a potential relative path.
     *
     * @param string $path
     *
     * @return null|string
     */
    protected function normalizePath(string $path): ?string
    {
        if (!$path) {
            // Obtain the current working directory (have to do it this way because when of symlinks with local composer)
            $path = dirname(getcwd() . DIRECTORY_SEPARATOR . $_SERVER['PHP_SELF']);

            // Load autoload relative to this location
            $path .= implode(self::DIRECTORY_SEPARATOR, ['..', '..', self::DIRNAME_COMPOSER_PUBLIC]);
        }

        if (substr($path, 0, 1) === '.') {
            // This is a relative path
            $path = getcwd() . DIRECTORY_SEPARATOR . $path;
        }
        return realpath($path) ?: null; // realpath just in case.
    }

    public function detect(string $path): Installation
    {
        $path = $this->normalizePath($path);
        if (!$path) {
            throw new InstallationNotFound('Unable to determine path.');
        }

        $version = $this->versionDetector->versionAtPath($path);

        if (!$version) {
            throw InstallationNotFound::atPath($path);
        }

        return new Installation($path, $version);
    }

}
