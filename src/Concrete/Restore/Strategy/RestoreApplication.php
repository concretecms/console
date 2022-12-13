<?php

declare(strict_types=1);

namespace Concrete\Console\Concrete\Restore\Strategy;

use Concrete\Console\Concrete\Restore\Restoration;

class RestoreApplication extends AbstractDirectoryExtractStrategy
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Console\Concrete\Restore\Strategy\AbstractDirectoryExtractStrategy::getExtractDirectory()
     */
    protected function getExtractDirectory(): string
    {
        return 'application';
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Console\Concrete\Restore\Strategy\AbstractDirectoryExtractStrategy::getExtractName()
     */
    protected function getExtractName(): string
    {
        return $this->getExtractDirectory();
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Console\Concrete\Restore\Strategy\AbstractDirectoryExtractStrategy::shouldClear()
     */
    protected function shouldClear(Restoration $job): bool
    {
        return false;
    }
}
