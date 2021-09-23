<?php

declare(strict_types=1);

namespace Concrete\Console\Concrete\Adapter;

use Concrete\Console\Concrete\Connection\ConnectionInterface;

interface AdapterInterface
{

    /**
     * Attach to a concrete5 site
     * @param string $path The path to attach to
     *
     * @return ConnectionInterface
     */
    public function attach(string $path): ConnectionInterface;
}
