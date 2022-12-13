<?php

declare(strict_types=1);

namespace Concrete\Console\Concrete\Restore\Strategy;

use Concrete\Console\Concrete\Restore\Restoration;

class RestoreConfig extends AbstractDirectoryExtractStrategy
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Console\Concrete\Restore\Strategy\AbstractDirectoryExtractStrategy::getExtractDirectory()
     */
    protected function getExtractDirectory(): string
    {
        return 'application/config/generated_overrides';
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Console\Concrete\Restore\Strategy\AbstractDirectoryExtractStrategy::getExtractName()
     */
    protected function getExtractName(): string
    {
        return 'generated_overrides';
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Console\Concrete\Restore\Strategy\AbstractDirectoryExtractStrategy::shouldClear()
     */
    protected function shouldClear(Restoration $job): bool
    {
        return true;
    }
}
