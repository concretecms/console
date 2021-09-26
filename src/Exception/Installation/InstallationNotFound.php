<?php

declare(strict_types=1);

namespace Concrete\Console\Exception\Installation;

class InstallationNotFound extends \InvalidArgumentException
{
    public static function atPath(string $path): InstallationNotFound
    {
        return new self("Unable to find concrete version at path: {$path}'", 404);
    }
}
