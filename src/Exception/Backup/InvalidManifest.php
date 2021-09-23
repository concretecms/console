<?php

declare(strict_types=1);

namespace Concrete\Console\Exception\Backup;

class InvalidManifest extends \InvalidArgumentException
{

    public static function atPath(string $path): BackupNotFound
    {
        return new BackupNotFound('Invalid manifest found in backup at path: ' . $path, 400);
    }
}
