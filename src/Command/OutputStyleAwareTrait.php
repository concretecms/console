<?php

namespace Concrete\Console\Command;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\NullOutput;

trait OutputStyleAwareTrait
{

    /**
     * @var OutputStyle|null
     */
    protected $traitOutputStyle;

    public function setOutputStyle(OutputStyle $outputStyle): void
    {
        $this->traitOutputStyle = $outputStyle;
    }

    protected function getOutputStyle(): OutputStyle
    {
        return $this->traitOutputStyle ?: new OutputStyle(new ArgvInput([]), new NullOutput());
    }
}
