<?php

declare(strict_types=1);

namespace Concrete\Console\Installation\Detector;

use Concrete\Console\Installation\Version;

class Version7Detector implements DetectorInterface
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Console\Installation\Detector\DetectorInterface::versionAtPath()
     */
    public function versionAtPath(string $path): ?Version
    {
        $tryFiles = [
            'concrete/config/concrete.php',
            'public/concrete/config/concrete.php',
            'web/concrete/config/concrete.php',
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
        if ($contents && preg_match("/\s+'version'\s+=>\s+'(?<version>.+?)',\n/m", $contents, $matches)) {
            return isset($matches['version']) ? $matches['version'] : null;
        }

        return null;
    }
}
