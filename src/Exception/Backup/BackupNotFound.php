<?php

declare(strict_types=1);

namespace Concrete\Console\Exception\Backup;

class BackupNotFound extends \InvalidArgumentException
{
    public static function atPath(string $path): BackupNotFound
    {
        return new BackupNotFound('No backup found at path: ' . $path, 404);
    }
}
