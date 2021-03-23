<?php


namespace Concrete\Console\Concrete\Restore\Strategy;

use Concrete\Console\Command\OutputStyleAwareTrait;
use Concrete\Console\Concrete\Restore\Restoration;

class RestoreCore extends AbstractDirectoryExtractStrategy
{

    protected function getExtractDirectory(): string
    {
        return 'concrete';
    }

    protected function getExtractName(): string
    {
        return $this->getExtractDirectory();
    }

    protected function shouldClear(Restoration $job): bool
    {
        return false;
    }
}
