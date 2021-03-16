<?php

namespace Concrete\Console\Installation;

/**
 * Class Validator. Validates whether the installation that we're working with is valid.
 *
 * @package Concrete\Console\Installation
 */
class Validator
{

    const DIRNAME_CONCRETE = 'concrete';
    const FILENAME_DISPATCHER = 'dispatcher.php';

    public function isValid(Installation $installation): bool
    {
        if (!is_dir($installation->getPath())) {
            throw new \RuntimeException(sprintf('Unable to locate installation directory: %s', $installation->getPath()));
        }

        if (!file_exists(
            $installation->getPath() . DIRECTORY_SEPARATOR . self::DIRNAME_CONCRETE . DIRECTORY_SEPARATOR .
                self::FILENAME_DISPATCHER)) {
            throw new \RuntimeException(sprintf('Installation directory %s does not appear to be a valid Concrete installation', $installation->getPath()));
        }

        return true;
    }


}