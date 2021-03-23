<?php

namespace Concrete\Console\Concrete\Restore\Strategy;

use Concrete\Console\Concrete\Restore\Restoration;

class RestorePackages extends AbstractDirectoryExtractStrategy
{

    protected function getExtractDirectory(): string
    {
        return 'packages';
    }

    protected function getExtractName(): string
    {
        return $this->getExtractDirectory();
    }

    protected function shouldClear(Restoration $job): bool
    {
        return true;
    }
}
