<?php

declare(strict_types=1);

namespace Concrete\Console\Concrete\Adapter;

use Concrete\Console\Concrete\Connection\ApplicationEnabledConnectionInterface;
use Concrete\Console\Concrete\Connection\ApplicationEnabledConnection;

class ApplicationEnabledAdapter extends AbstractApplicationEnabledAdapter
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Console\Concrete\Adapter\AbstractApplicationEnabledAdapter::createConnection()
     */
    protected function createConnection(): ApplicationEnabledConnectionInterface
    {
        return new ApplicationEnabledConnection();
    }
}
