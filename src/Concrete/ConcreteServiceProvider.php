<?php

namespace Concrete\Console\Concrete;

use Concrete\Console\Concrete\Adapter\AdapterFactory;
use Concrete\Console\Concrete\Connection\ConnectionAwareInterface;
use Concrete\Console\Concrete\Connection\ConnectionInterface;
use Concrete\Console\Installation\Installation;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

class ConcreteServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{

    /**
     * @var mixed[]
     */
    protected $provides = [
        ClientInterface::class,
        ConnectionInterface::class,
    ];

    public function register()
    {
        $app = $this->getLeagueContainer();

        $app->add(ClientInterface::class, function (): ClientInterface {
            $installation = $this->getContainer()->get(Installation::class);
            $adapter = $this->getContainer()->get(AdapterFactory::class)
                ->forVersion($installation->getVersion()->getVersion());

            return new Client($adapter);
        });

        $app->add(ConnectionInterface::class, function (): ConnectionInterface {
            $client = $this->getContainer()->get(ClientInterface::class);
            $installation = $this->getContainer()->get(Installation::class);

            return $client->connect($installation->getPath());
        }, true);
    }

    public function boot()
    {
        $app = $this->getLeagueContainer();

        // Add inflector for version7 connection
        $app->inflector(ConnectionAwareInterface::class)
            ->invokeMethod('setConnection', [ConnectionInterface::class]);
    }
}
