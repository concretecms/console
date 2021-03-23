<?php


namespace Concrete\Console\Command;


use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\NullOutput;

trait OutputStyleAwareTrait
{

    /**
     * @var OutputStyle|null
     */
    protected $__outputStyle;

    public function setOutputStyle(OutputStyle $outputStyle): void
    {
        $this->__outputStyle = $outputStyle;
    }

    protected function getOutputStyle(): OutputStyle
    {
        return $this->__outputStyle ?: new OutputStyle(new ArgvInput([]), new NullOutput());
    }

}
