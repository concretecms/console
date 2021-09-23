<?php

declare(strict_types=1);

namespace Concrete\Console\Concrete\Restore;

use League\Container\ContainerAwareInterface as ContainerAwareInterfaceAlias;
use League\Container\ContainerAwareTrait;

class RestorationManagerBuilder implements ContainerAwareInterfaceAlias
{
    use ContainerAwareTrait;

    protected $manager;

    public function __construct(RestorationManager $manager)
    {
        $this->manager = $manager;
    }

    public function restoreDatabase(bool $skip = false): RestorationManagerBuilder
    {
        return $this->addStrategy(Strategy\RestoreDatabase::class, $skip);
    }

    public function restoreCore(bool $skip = false): RestorationManagerBuilder
    {
        return $this->addStrategy(Strategy\RestoreCore::class, $skip);
    }

    public function restorePackages(bool $skip = false): RestorationManagerBuilder
    {
        return $this->addStrategy(Strategy\RestorePackages::class, $skip);
    }

    public function restoreApplication(bool $skip = false): RestorationManagerBuilder
    {
        return $this->addStrategy(Strategy\RestoreApplication::class, $skip);
    }

    public function restoreIndex(bool $skip = false): RestorationManagerBuilder
    {
        return $this->addStrategy(Strategy\RestoreIndex::class, $skip);
    }

    public function restoreConfig(bool $skip = false): RestorationManagerBuilder
    {
        return $this->addStrategy(Strategy\RestoreConfig::class, $skip);
    }

    public function restoreFiles(bool $skip = false): RestorationManagerBuilder
    {
        return $this->addStrategy(Strategy\RestoreStorageLocations::class, $skip);
    }

    public function enableMaintenancePage(bool $skip = false): RestorationManagerBuilder
    {
        return $this->addStrategy(Strategy\EnableMaintenanceMode::class, $skip);
    }

    public function finalize(bool $skip = false): RestorationManagerBuilder
    {
        return $this->addStrategy(Strategy\Finalize::class, $skip);
    }

    public function resolve(): RestorationManager
    {
        return $this->manager;
    }

    protected function addStrategy(string $strategy, bool $skip = false): RestorationManagerBuilder
    {
        $this->manager->addStrategy($this->container->get($strategy), $skip);
        return $this;
    }
}
