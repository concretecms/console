<?php

namespace Concrete\Console\Installation;

use Concrete\Console\Installation\InstallationAwareInterface;
use Concrete\Console\Installation\Detector\BaseDetector;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputInterface;

class InstallationServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{

    /**
     * @var mixed[]
     */
    protected $provides = [
        Installation::class,
        InstallationDetector::class,
    ];

    public function register()
    {
        $this->getLeagueContainer()->add(Installation::class, function(): ?Installation {
            $factory = $this->getContainer()->get(InstallationDetector::class);
            $input = $this->getContainer()->get(InputInterface::class)->getOption('instance');
            if (!is_string($input)) {
                throw new \RuntimeException('Invalid instance value given, input must be a string.');
            }

            return $factory->detect($input);
        }, true);

        $this->getLeagueContainer()
            ->add(InstallationDetector::class)
            ->addArgument(BaseDetector::class);
    }

    public function boot()
    {
        $this->getLeagueContainer()->inflector(InstallationAwareInterface::class)
            ->invokeMethod('setInstallation', [Installation::class]);
    }
}
