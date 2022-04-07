<?php

declare(strict_types=1);

namespace Concrete\Console\Installation;

trait InstallationAwareTrait
{

    /** @var Installation */
    protected $traitInstallation;

    public function setInstallation(Installation $connection): void
    {
        $this->traitInstallation = $connection;
    }

    protected function getInstallation(): Installation
    {
        return $this->traitInstallation;
    }

    protected static function getInstanceOptionSillyExpression(): string
    {
        return '[-I|' . static::getInstanceOptionName() . '=]';
    }

    protected static function getInstanceOptionName(string $prefix = '--'): string
    {
        return "{$prefix}instance";
    }

    protected static function getInstanceOptionDescription(): string
    {
        return 'Specify the concrete directory';
    }

    protected static function getInstanceOptionDefaultValue(): string
    {
        return '.';
    }
}
