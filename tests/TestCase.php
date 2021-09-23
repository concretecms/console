<?php

declare(strict_types=1);

namespace Concrete\Console;

class TestCase extends \PHPUnit\Framework\TestCase
{

    /**
     * @beforeClass
     */
    public static function concreteSetup(): void
    {
        // Declare c5 execute
        if (!defined('C5_EXECUTE')) {
            define('C5_EXECUTE', 'concrete5/console');
        }
    }

    /**
     * @after
     */
    public function mockeryTearDown(): void
    {
        \Mockery::close();
    }
}
