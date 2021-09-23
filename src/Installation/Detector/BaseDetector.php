<?php

declare(strict_types=1);

namespace Concrete\Console\Installation\Detector;

use Concrete\Console\Installation\Version;
use Concrete\Core\Multilingual\Service\Detector;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;

class BaseDetector implements DetectorInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var bool
     */
    protected $mapped = false;

    /** @var (string|DetectorInterface)[] */
    protected $detectors = [
        Version6Detector::class,
        Version7Detector::class,
    ];

    public function versionAtPath(string $path): ?Version
    {
        $detectors = $this->inflatedDetectors();
        foreach ($detectors as $detector) {
            if ($version = $detector->versionAtPath($path)) {
                return $version;
            }
        }

        return null;
    }

    /**
     * @return DetectorInterface[]
     */
    protected function inflatedDetectors(): array
    {
        $detectors = $this->detectors;
        if (!$this->mapped) {
            $detectors = array_map(function ($detector) {
                if (is_string($detector)) {
                    $detector = $this->getContainer()->get($detector);
                }

                return $detector;
            }, $detectors);

            $this->mapped = true;
            $this->detectors = $detectors;
        }

        /** @psalm-var DetectorInterface[] */
        return $detectors;
    }
}
