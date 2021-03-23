<?php


namespace Concrete\Console\Command;

use Symfony\Component\Console\Style\SymfonyStyle;

class OutputStyle extends SymfonyStyle
{

    public function outputStep(string $step, string $suffix = '... ', string $prefix = '  - '): void
    {
        $this->newLine();
        $this->write($prefix . rtrim($step, '. ') . $suffix);
    }

    public function outputDone(string $doneText = 'Done!', string $tag = '<fg=cyan>'): void
    {
        $this->write($tag . $doneText . '</>');
    }

    public function outputDryrun(): void
    {
        $this->outputDone('Done! <fg=yellow;options=bold>(Dry run)</>');
    }

    public function outputFinal(): void
    {
        $this->outputStep('', '', '  -');
    }

}
