<?php

namespace Concrete\Console\Concrete\Restore\Strategy;

use Concrete\Console\Concrete\Restore\Restoration;
use Concrete\Console\Concrete\Restore\StrategyInterface;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

class RestoreConfig extends AbstractDirectoryExtractStrategy
{

    protected function getExtractDirectory(): string
    {
        return 'application/config/generated_overrides';
    }

    protected function getExtractName(): string
    {
        return 'generated_overrides';
    }

    protected function shouldClear(Restoration $job): bool
    {
        return true;
    }
}
