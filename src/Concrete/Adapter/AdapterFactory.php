<?php

declare(strict_types=1);

namespace Concrete\Console\Concrete\Adapter;

use Composer\Semver\Comparator;
use Concrete\Console\Exception\Installation\VersionMismatch;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;

class AdapterFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function forVersion(string $version): AdapterInterface
    {
        if (Comparator::lessThan($version, '5.6')) {
            throw VersionMismatch::expected('> 5.6.0', $version);
        }

        $map = [
            '5.6.9999' => Version6Adapter::class,
            '9.9999999' => ApplicationEnabledAdapter::class,
        ];

        foreach ($map as $test => $adapterClass) {
            if (Comparator::lessThanOrEqualTo($version, $test)) {
                return $this->getContainer()->get($adapterClass);
            }
        }

        throw VersionMismatch::expected('< 10.0.0', $version);
    }
}
