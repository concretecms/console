<?php


namespace Concrete\Console\Installation\Detector;


use Concrete\Console\Installation\DatabaseCredentials;
use Concrete\Console\Installation\Version;

class Version6Detector implements DetectorInterface
{

    public function versionAtPath(string $path): ?Version
    {
        $tryFiles = [
            'concrete/config/version.php',
            'public/concrete/config/version.php',
            'web/concrete/config/version.php',
        ];

        foreach ($tryFiles as $file) {
            $versionFile = $path . '/' . $file;

            if (file_exists($versionFile) && $version = $this->loadVersion($versionFile)) {
                return Version::fromVersionString($version);
            }
        }

        return null;
    }
    protected function loadVersion(string $versionFile): ?string
    {
        $contents = file_get_contents($versionFile, false, null, 0, 1000);
        $matches = [];
        if ($contents && preg_match("/\\\$APP_VERSION = '(?<version>.+?)';/", $contents, $matches)) {
            return isset($matches['version']) ? $matches['version'] : null;
        }

        return null;
    }
}
