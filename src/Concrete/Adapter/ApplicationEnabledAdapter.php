<?php


namespace Concrete\Console\Concrete\Adapter;

use Concrete\Console\Concrete\Connection\ApplicationEnabledConnectionInterface;
use Concrete\Console\Concrete\Connection\ApplicationEnabledConnection;

class ApplicationEnabledAdapter extends AbstractApplicationEnabledAdapter
{
    protected function createConnection(): ApplicationEnabledConnectionInterface
    {
        return new ApplicationEnabledConnection();
    }
}
