<?php

namespace Concrete\Console\Installation;

/**
 * Class Validator. Validates whether the installation that we're working with is valid.
 *
 * @package Concrete\Console\Installation
 */
class Validator
{

    public const DIRNAME_CONCRETE = 'concrete';
    public const FILENAME_DISPATCHER = 'dispatcher.php';

    public function isValid(Installation $installation): bool
    {
        if (!is_dir($installation->getPath())) {
            throw new \RuntimeException(
                sprintf(
                    'Unable to locate installation directory: %s',
                    $installation->getPath()
                )
            );
        }

        $path = implode(
            DIRECTORY_SEPARATOR,
            [$installation->getPath(), self::DIRNAME_CONCRETE, self::FILENAME_DISPATCHER]
        );

        if (!file_exists($path)) {
            throw new \RuntimeException(
                sprintf(
                    'Installation directory %s does not appear to be a valid Concrete installation',
                    $installation->getPath()
                )
            );
        }

        return true;
    }
}
