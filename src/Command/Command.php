<?php

namespace Concrete\Console\Command;

use Concrete\Console\Application;
use Concrete\Console\Concrete\Connection\ApplicationEnabledConnectionInterface;
use Concrete\Console\Concrete\Connection\ConnectionAwareInterface;
use Concrete\Console\Concrete\Connection\ConnectionAwareTrait;
use Concrete\Console\Exception\Installation\VersionMismatch;
use Concrete\Console\Installation\InstallationAwareInterface;
use Concrete\Console\Installation\InstallationAwareTrait;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;

abstract class Command implements ContainerAwareInterface, OutputStyleAwareInterface, OutputInterface, StyleInterface,
                                  ConsoleAwareInterface, CommandGroupInterface, ConnectionAwareInterface, InstallationAwareInterface
{

    /** @var OutputStyle */
    protected $output;

    /** @var Application */
    protected $console;

    use ContainerAwareTrait;
    use ConnectionAwareTrait;
    use InstallationAwareTrait;

    /**
     * @param OutputStyle $outputStyle
     * @return void
     */
    public function setOutputStyle(OutputStyle $outputStyle): void
    {
        $this->output = $outputStyle;
    }

    /**
     * @inheritDoc
     *
     * @param string|array $messages
     * @param bool $newline
     * @param int $options
     *
     * @return void
     */
    public function write($messages, $newline = false, $options = 0)
    {
        $this->output->write($messages, $newline, $options);
    }

    /**
     * @inheritDoc
     *
     * @param string|array $messages
     * @param int $options
     *
     * @return void
     */
    public function writeln($messages, $options = 0)
    {
        $this->output->writeln($messages, $options);
    }

    /**
     * @inheritDoc
     * @param mixed $level
     * @return void
     */
    public function setVerbosity($level)
    {
        $this->output->setVerbosity($level);
    }

    /**
     * @inheritDoc
     */
    public function getVerbosity(): int
    {
        return $this->output->getVerbosity();
    }

    /**
     * @inheritDoc
     */
    public function isQuiet(): bool
    {
        return $this->output->isQuiet();
    }

    /**
     * @inheritDoc
     */
    public function isVerbose(): bool
    {
        return $this->output->isVerbose();
    }

    /**
     * @inheritDoc
     */
    public function isVeryVerbose(): bool
    {
        return $this->output->isVeryVerbose();
    }

    /**
     * @inheritDoc
     */
    public function isDebug(): bool
    {
        return $this->output->isDebug();
    }

    /**
     * @inheritDoc
     */
    public function isDecorated(): bool
    {
        return $this->output->isDebug();
    }

    /**
     * @inheritDoc
     */
    public function getFormatter(): OutputFormatterInterface
    {
        return $this->output->getFormatter();
    }

    /**
     * @inheritDoc
     */
    public function title($message): void
    {
        $this->output->title($message);
    }

    /**
     * @inheritDoc
     */
    public function section($message): void
    {
        $this->output->section($message);
    }

    /**
     * @inheritDoc
     */
    public function listing(array $elements): void
    {
        $this->output->listing($elements);
    }

    /**
     * @inheritDoc
     */
    public function text($message): void
    {
        $this->output->text($message);
    }

    /**
     * @inheritDoc
     */
    public function success($message): void
    {
        $this->output->success($message);
    }

    /**
     * @inheritDoc
     */
    public function error($message): void
    {
        $this->output->error($message);
    }

    /**
     * @inheritDoc
     */
    public function warning($message): void
    {
        $this->output->warning($message);
    }

    /**
     * @inheritDoc
     */
    public function note($message): void
    {
        $this->output->note($message);
    }

    /**
     * @inheritDoc
     */
    public function caution($message): void
    {
        $this->output->caution($message);
    }

    /**
     * @inheritDoc
     */
    public function table(array $headers, array $rows): void
    {
        $this->output->table($headers, $rows);
    }

    /**
     * @inheritDoc
     */
    public function ask($question, $default = null, $validator = null): void
    {
        $this->output->ask($question, $default, $validator);
    }

    /**
     * @inheritDoc
     */
    public function askHidden($question, $validator = null): void
    {
        $this->output->askHidden($question, $validator);
    }

    /**
     * @inheritDoc
     */
    public function confirm($question, $default = true): bool
    {
        return $this->output->confirm($question, $default);
    }

    /**
     * @inheritDoc
     * @param mixed $question
     * @param string|int|null $default
     * @return void
     */
    public function choice($question, array $choices, $default = null)
    {
        $this->output->choice($question, $choices, $default);
    }

    /**
     * @inheritDoc
     */
    public function newLine($count = 1): void
    {
        $this->output->newLine($count);
    }

    /**
     * @inheritDoc
     */
    public function progressStart($max = 0): void
    {
        $this->output->progressStart($max);
    }

    /**
     * @inheritDoc
     */
    public function progressAdvance($step = 1): void
    {
        $this->output->progressAdvance($step);
    }

    /**
     * @inheritDoc
     */
    public function progressFinish(): void
    {
        $this->output->progressFinish();
    }

    /**
     * @inheritDoc
     */
    public function setDecorated($decorated): void
    {
        $this->output->setDecorated($decorated);
    }

    /**
     * @inheritDoc
     */
    public function setFormatter(OutputFormatterInterface $formatter): void
    {
        $this->output->setFormatter($formatter);
    }

    public function setConsole(Application $application): void
    {
        $this->console = $application;
    }

    protected function getConsole(): Application
    {
        return $this->console;
    }

    public function getApplication(): \Concrete\Core\Application\Application
    {
        $connection = $this->getConnection();
        if (!$connection || !$connection instanceof ApplicationEnabledConnectionInterface) {
            throw new VersionMismatch('This command can only run on Application Enabled concrete installs.');
        }

        return $connection->getApplication();
    }

}
