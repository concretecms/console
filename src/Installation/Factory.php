<?php

namespace Concrete\Console\Installation;

class Factory
{

    const DIRNAME_COMPOSER_PUBLIC = 'public';

    /**
     * Deals with a potential relative path.
     *
     * @param string $path
     */
    public function normalizePath(string $path)
    {
        if (substr($path, 0, 1) === '.') {
            // This is a relative path
            $path = getcwd() . DIRECTORY_SEPARATOR . $path;
        }
        return realpath($path); // realpath just in case.
    }

    /**
     * Creates an installation record from a path to a particular directory.
     * @param string $path
     */
    public function createFromPath(string $path): Installation
    {
        return new Installation($path);
    }

    /**
     * Creates an installation object without a specific path passed. Used with composer installs.
     */
    public function detectInstallation(): Installation
    {
        // Obtain the current working directory (have to do it this way because when of symlinks with local composer)
        $path = dirname(getcwd() . DIRECTORY_SEPARATOR . $_SERVER['PHP_SELF']);
        // Load autoload relative to this location
        $path = realpath($path . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . self::DIRNAME_COMPOSER_PUBLIC);
        return new Installation($path);
    }




}