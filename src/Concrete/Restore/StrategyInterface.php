<?php

declare(strict_types=1);

namespace Concrete\Console\Concrete\Restore;

interface StrategyInterface
{

    public function restore(Restoration $job): bool;
}
