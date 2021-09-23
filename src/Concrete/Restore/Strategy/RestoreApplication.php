<?php

declare(strict_types=1);

namespace Concrete\Console\Concrete\Restore\Strategy;

use Concrete\Console\Command\OutputStyleAwareTrait;
use Concrete\Console\Concrete\Restore\Restoration;

class RestoreApplication extends AbstractDirectoryExtractStrategy
{

    protected function getExtractDirectory(): string
    {
        return 'application';
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
