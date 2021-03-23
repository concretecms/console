<?php

use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

function dd(...$args): void
{
    var_dump(...$args);
    die();
}

/**
 * Safely build a symfony process instance
 *
 * @param string|array $commandline
 * @param string|null $cwd
 * @param array|null $env
 * @param mixed $input
 * @param int $timeout
 * @param array|null $options
 * @return Process
 *
 * @psalm-suppress DeprecatedMethod, ImpureMethodCall
 * @psalm-pure
 */
function process($commandline, $cwd = null, array $env = null, $input = null, $timeout = 60, array $options = null): Process
{
    $process = null;
    if (is_string($commandline)) {
        if (method_exists(Process::class, 'fromShellCommandLine')) {
            /** @var Process $process */
            $process = Process::fromShellCommandLine($commandline, $cwd, $env, $input, $timeout);
        }
    }

    if (!$process) {
        $process = new Process($commandline, $cwd, $env, $input, $timeout);
    }

    if ($options) {
        $process->setOptions($options);
    }

    return $process;
}

function stdoutToOutput(ConsoleOutputInterface $output): Closure
{
    return function($message, $channel) use ($output): void {
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
     * @param array $array
     * @param string $key
     * @psalm-param T $default
     * @return T|mixed
     */
    function dot_get(array $array, string $key, $default = null)
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
    function class_basename($classOrInstance): string {
        if (!is_string($classOrInstance)) {
            $classOrInstance = get_class($classOrInstance);
        }

        $segments = explode('\\', $classOrInstance);
        return array_pop($segments);
    }
}
