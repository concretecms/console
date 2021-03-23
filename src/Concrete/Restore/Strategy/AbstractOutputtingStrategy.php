<?php


namespace Concrete\Console\Concrete\Restore\Strategy;


use Concrete\Console\Command\OutputStyleAwareInterface;
use Concrete\Console\Command\OutputStyleAwareTrait;
use Concrete\Console\Concrete\Restore\StrategyInterface;

abstract class AbstractOutputtingStrategy implements OutputStyleAwareInterface, StrategyInterface
{

    use OutputStyleAwareTrait;

}
