<?php


namespace Concrete\Console\Concrete\Restore;


use Concrete\Console\Command\OutputStyle;
use Concrete\Console\Command\OutputStyleAwareInterface;

class RestorationManager implements StrategyInterface, OutputStyleAwareInterface
{

    public const RESULT_SUCCESS = 1;
    public const RESULT_FAILURE = 2;
    public const RESULT_SKIPPED = 3;

    /** @var OutputStyle */
    protected $outputStyle;

    /** @var array<string, array{0: StrategyInterface, 1: bool}> */
    protected $strategies = [];

    public function addStrategy(StrategyInterface $strategy, bool $skip = false): RestorationManager
    {
        $this->strategies[get_class($strategy)] = [$strategy, $skip];
        return $this;
    }

    public function restoreGenerator(Restoration $job): \Generator
    {
        $failed = 0;
        $succeeded = 0;
        foreach ($this->strategies as $strategyArray) {
            [$strategy, $skip] = $strategyArray;
            $this->outputStyle->write(' Starting <fg=green;options=bold>' . snake_case(class_basename($strategy), ' ') . '</> step...');

            if (!$skip) {
                if ($strategy->restore($job)) {
                    $this->outputStyle->writeln(' <fg=green>Success!</>');
                    $succeeded++;
                    yield self::RESULT_SUCCESS;
                } else {
                    $this->outputStyle->writeln(' <fg=red>Failed.</>');
                    $failed++;
                    yield self::RESULT_FAILURE;
                }
            } else {
                $this->outputStyle->writeln(' <fg=yellow>Skipped.</>');
                yield self::RESULT_SKIPPED;
            }
        }

        $this->outputStyle->newLine();
        $this->outputStyle->writeln('Finished restoration' . ($failed ? " with {$failed} failures." : '!'));

        return $failed === 0;
    }

    public function restore(Restoration $job): bool
    {
        $generator = $this->restoreGenerator($job);
        // Resolve the generator completely
        iterator_to_array($generator);

        return $generator->getReturn();
    }

    public function setOutputStyle(OutputStyle $outputStyle): void
    {
        $this->outputStyle = $outputStyle;
    }
}
