<?php

declare(strict_types=1);

namespace Concrete\Console\Command;

interface OutputStyleAwareInterface
{
    public function setOutputStyle(OutputStyle $outputStyle): void;
}
