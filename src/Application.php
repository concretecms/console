<?php

declare(strict_types=1);

namespace Concrete\Console;

use Concrete\Console\Command\CommandProvider;
use Concrete\Console\Command\OutputStyle;
use Concrete\Console\Command\OutputStyleAwareInterface;
use League\Container\Container;
use Silly\Application as SillyApplication;
use Silly\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method Container getContainer()
 */
class Application extends SillyApplication
{

    /**
     * @var bool
     */
    protected $registered = false;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var OutputStyle
     */
    protected $style;

    public function __construct(Container $container)
    {
        // This value will be replaced automatically when building the phar file.
        $version = '@concrete_version@';
        /** @psalm-suppress RedundantCondition */
        parent::__construct('Concrete Console', $version === '@concrete_' . 'version@' ? '' : ltrim($version, 'v'));
        $this->useContainer($container, true, true);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Symfony\Component\Console\Application::doRun()
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->style = new OutputStyle($input, $output);

        try {
            // Loads in default stuff
            $input->bind($this->getDefinition());
        } catch (\Throwable $e) {
            // Errors must be ignored, full binding/validation happens later when the command is known.
        }

        // Add input, output, and OutputStyle to container
        $this->getContainer()->add(InputInterface::class, $input);
        $this->getContainer()->add(OutputInterface::class, $output);
        $this->getContainer()->add(ConsoleOutputInterface::class, $output);
        $this->getContainer()->add(OutputStyle::class, $this->style);
        $this->getContainer()->inflector(OutputStyleAwareInterface::class)
            ->invokeMethod('setOutputStyle', [OutputStyle::class]);

        // Add commands
        CommandProvider::register($this->getContainer(), $this);

        return parent::doRun($input, $output);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Symfony\Component\Console\Application::getHelp()
     */
    public function getHelp()
    {
        $artWidth = 42;

        // Get the width of the title, excluding the version on purpose
        $titleWidth = strlen($this->getName());

        // Make help a little fancy
        return implode('', [
            file_get_contents(__DIR__ . '/../art/concrete-symfony.txt'),
            str_repeat(' ', (int) ceil(($artWidth - $titleWidth) / 2)),
            "<options=bold,underscore>{$this->getName()} <fg=cyan>{$this->getVersion()}</></>",
        ]);
    }

    public function getInput(): InputInterface
    {
        return $this->input;
    }

    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    public function getOutputStyle(): OutputStyle
    {
        return $this->style;
    }
}
