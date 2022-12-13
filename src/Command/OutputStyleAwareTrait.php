<?php

declare(strict_types=1);

namespace Concrete\Console\Command;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\NullOutput;

trait OutputStyleAwareTrait
{
    /**
     * @var OutputStyle|null
     */
    protected $output;

    /**
     * @see \Concrete\Console\Command\OutputStyleAwareInterface::setOutputStyle()
     */
    public function setOutputStyle(OutputStyle $outputStyle): void
    {
        $this->output = $outputStyle;
    }

    protected function getOutputStyle(): OutputStyle
    {
        return $this->output ?: new OutputStyle(new ArgvInput([]), new NullOutput());
    }
}
