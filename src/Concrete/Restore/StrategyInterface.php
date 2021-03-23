<?php
namespace Concrete\Console\Concrete\Restore;

interface StrategyInterface
{

    public function restore(Restoration $job): bool;

}
