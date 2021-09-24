<?php

declare(strict_types=1);

use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Quick way to call var_dump() and die().
 *
 * @param mixed $args
 */
function dd(...$args): void
{
    /**
     * @psalm-suppress ForbiddenCode
     */
    var_dump(...$args);
    die();
}

/**
 * Safely build a symfony process instance
 *
 * @param string|array $commandline
 * @param mixed $input
 *
 * @psalm-suppress ImpureMethodCall
 * @psalm-pure
 */
function process($commandline, ?string $cwd = null, ?array $env = null, $input = null, ?float $timeout = 60, ?array $options = null): Process
{
    $process = null;
    if (is_string($commandline)) {
        if (method_exists(Process::class, 'fromShellCommandLine')) {
            $process = Process::fromShellCommandLine($commandline, $cwd, $env, $input, $timeout);
        }
    }

    if (!$process) {
        /**
         * @psalm-suppress PossiblyInvalidArgument
         */
        $process = new Process($commandline, $cwd, $env, $input, $timeout);
    }

    if ($options && method_exists($process, 'setOptions')) {
        $process->setOptions($options);
    }

    return $process;
}

function stdoutToOutput(ConsoleOutputInterface $output): Closure
{
    return
        /**
         * @param string|iterable $message
         */
        static function($message, string $channel) use ($output): void {
            switch ($channel) {
                case 'STDOUT':
                    $output->writeln($message);
                    break;
                case 'STDERR':
                    $output->getErrorOutput()->writeln($message, OutputInterface::OUTPUT_RAW);
                    break;
                default:
                    dd('Unknown message type: ' . $channel);
            }
        };
}

if (!function_exists('dot_get')) {
    /**
     * @template T
     * @psalm-param T $default
     * @return T|mixed
     */
    function dot_get(array $array, ?string $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        if (strpos($key, '.') === false) {
            return isset($array[$key]) ? $array[$key] : $default;
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                /**
                 * @psalm-suppress MixedAssignment
                 */
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }
}

if (!function_exists('snake_case')) {
    function snake_case(string $original, string $delimiter = '_'): string
    {
        $result = $original;
        if (! ctype_lower($result)) {
            $result = preg_replace('/\s+/u', '', ucwords($result));

            $result = mb_strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $result), 'UTF-8');
        }

        return $result;
    }
}

if (!function_exists('class_basename')) {
    /**
     * @param string|object $classOrInstance
     */
    function class_basename($classOrInstance): string
    {
        if (!is_string($classOrInstance)) {
            $classOrInstance = get_class($classOrInstance);
        }

        $segments = explode('\\', $classOrInstance);
        return array_pop($segments);
    }
}
