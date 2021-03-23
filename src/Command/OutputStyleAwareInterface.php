<?php
namespace Concrete\Console\Command;

interface OutputStyleAwareInterface
{

    public function setOutputStyle(OutputStyle $outputStyle): void;

}
