<?php


namespace Concrete\Console\Command;

use League\CLImate\CLImate;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Output extends CLImate implements ConsoleOutputInterface
{

    /** @var ?ConsoleOutput */
    protected $consoleOutput;

    public function __construct()
    {
        parent::__construct();
        $this->addArt(__DIR__ . '/../../art');
    }

    protected function getConsole(): ConsoleOutput
    {
        if (!$this->consoleOutput) {
            $this->consoleOutput = new ConsoleOutput();
        }

        return $this->consoleOutput;
    }

    public function getErrorOutput()
    {
        return $this->getConsole()->getErrorOutput();
    }

    public function setErrorOutput(OutputInterface $error): void
    {
        $this->getConsole()->setErrorOutput($error);
    }

    public function write($messages, $newline = false, $options = 0): void
    {
        $this->getConsole()->write($messages, $newline, $options);
    }

    public function writeln($messages, $options = 0): void
    {
        $this->getConsole()->writeln($messages, $options);
    }

    public function setVerbosity($level): void
    {
        $this->getConsole()->setVerbosity($level);
    }

    public function getVerbosity(): int
    {
        return $this->getConsole()->getVerbosity();
    }

    public function isQuiet(): bool
    {
        return $this->getConsole()->isQuiet();
    }

    public function isVerbose(): bool
    {
        return $this->getConsole()->isVerbose();
    }

    public function isVeryVerbose(): bool
    {
        return $this->getConsole()->isVeryVerbose();
    }

    public function isDebug(): bool
    {
        return $this->getConsole()->isDebug();
    }

    public function setDecorated($decorated): void
    {
        $this->getConsole()->setDecorated($decorated);
    }

    public function isDecorated(): bool
    {
        return $this->getConsole()->isDecorated();
    }

    public function setFormatter(OutputFormatterInterface $formatter): void
    {
        $this->getConsole()->setFormatter($formatter);
    }

    public function getFormatter(): OutputFormatterInterface
    {
        return $this->getConsole()->getFormatter();
    }
}
