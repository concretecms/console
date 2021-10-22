<?php

declare(strict_types=1);

namespace Concrete\Console\Installation;

use Concrete\Console\Concrete\Connection\ApplicationEnabledConnectionInterface;
use Concrete\Console\Concrete\Connection\ConnectionInterface;
use Concrete\Console\Exception\Backup\BackupNotFound;
use Concrete\Console\Exception\Backup\InvalidManifest;

class ManifestFactory
{
    public function forBackup(string $path): Manifest
    {
        if (!file_exists($path)) {
            throw BackupNotFound::atPath($path);
        }

        $backupFile = new \PharData($path);
        $contents = json_decode($backupFile->offsetGet('manifest.json')->getContent(), true);

        if (!$contents) {
            throw InvalidManifest::atPath($path);
        }

        return Manifest::jsonDeserialize($contents);
    }

    public function forConnection(ConnectionInterface $connection): ?Manifest
    {
        if (!$connection instanceof ApplicationEnabledConnectionInterface) {
            return null;
        }

        // @TODO Implement this factory method
        return new Manifest();
    }
}
