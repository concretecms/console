<?php

declare(strict_types=1);

namespace Concrete\Console\Command;

use Concrete\Console\Application;
use Concrete\Console\Concrete\Connection\ApplicationEnabledConnectionInterface;
use Concrete\Console\Concrete\Connection\ConnectionAwareInterface;
use Concrete\Console\Concrete\Connection\ConnectionAwareTrait;
use Concrete\Console\Exception\Installation\VersionMismatch;
use Concrete\Console\Installation\InstallationAwareInterface;
use Concrete\Console\Installation\InstallationAwareTrait;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Silly\Command\Command as SillyCommand;

abstract class Command implements
    ContainerAwareInterface,
    OutputStyleAwareInterface,
    ConsoleAwareInterface,
    CommandGroupInterface,
    ConnectionAwareInterface,
    InstallationAwareInterface
{
    use ContainerAwareTrait;
    use OutputStyleAwareTrait;
    use ConsoleAwareTrait;
    use ConnectionAwareTrait;
    use InstallationAwareTrait;

    /**
     * Additional options to add separate from declared commands
     *
     * @var array<int, array{long: string, short: string, description: string, default: mixed, optional: boolean}>
     */
    protected static $extendedOptions = [
        [
            'long' => 'instance',
            'short' => 'I',
            'description' => 'Specify the concrete5 directory',
            'default' => '.',
            'optional' => true,
        ]
    ];

    /**
     * @throws \Concrete\Console\Exception\Installation\VersionMismatch
     */
    public function getApplication(): \Concrete\Core\Application\Application
    {
        $connection = $this->getConnection();
        if (!$connection || !$connection instanceof ApplicationEnabledConnectionInterface) {
            throw new VersionMismatch('This command can only run on Application Enabled concrete installs.');
        }

        return $connection->getApplication();
    }

    /**
     * Static function for resolving the command instance with $extendedOptions included.
     *
     * @param Application $console
     * @param string $expression
     * @param string|callable $callable
     * @param array $aliases
     *
     * @return SillyCommand
     */
    public static function command(
        Application $console,
        string $expression,
        $callable,
        array $aliases = []
    ): SillyCommand {
        $extension = [];
        $descriptions = [];
        $defaults = [];
        foreach (static::$extendedOptions as $option) {
            [
                'long' => $long,
                'short' => $short,
                'description' => $description,
                'default' => $default,
                'optional' => $optional
            ] = $option;

            // Resolve the extension string
            $optionalAddition = $optional ? '=' : '';
            $shortFlag = $short ? "-{$short}|" : '';
            $longFlag = "--{$long}{$optionalAddition}";
            $extension[] = "[{$shortFlag}{$longFlag}]";

            // resolve defaults and descriptions
            $defaults[$long] = $default;
            $descriptions['--' . $long] = $description;
        }

        $extension = $extension ? ' ' . implode(' ', $extension) : '';

        // Create a new command with our extension string added
        return $console->command("{$expression}{$extension}", $callable, $aliases)
            ->descriptions('', $descriptions)
            ->defaults($defaults);
    }
}
