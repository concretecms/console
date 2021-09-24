<?php

declare(strict_types=1);

namespace Concrete\Console\Command\Phar;

use Concrete\Console\Application;
use Concrete\Console\Command\CommandGroupInterface;
use Concrete\Console\Command\ConsoleAwareInterface;
use Concrete\Console\Command\ConsoleAwareTrait;
use Concrete\Console\Command\OutputStyleAwareInterface;
use Concrete\Console\Command\OutputStyleAwareTrait;
use League\Container\Container;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Phar;
use RuntimeException;

class SelfUpdateCommand implements
    ContainerAwareInterface,
    OutputStyleAwareInterface,
    ConsoleAwareInterface,
    CommandGroupInterface
{
    use ContainerAwareTrait;
    use OutputStyleAwareTrait;
    use ConsoleAwareTrait;

    private const LASTESTRELEASE_PATTERN = 'https://github.com/concrete5/console/releases/latest/download/%s';

    public static function register(Container $container, Application $console): void
    {
        $console->command('self-update', self::class)
            ->descriptions(
                'Update concrete.phar to the latest version.'
            )
        ;
    }

    /**
     * @throws \RuntimeException
     */
    public function __invoke(Application $console): int
    {
        $this->checkRequirements();
        $myVersion = $this->getMyVersion();
        $latestVersion = $this->getMostRecentAvailableVersion();
        if (version_compare($myVersion, $latestVersion) >= 0) {
            if (!$this->output->isQuiet()) {
                $this->output->writeln(
<<<EOT
<comment>You are already using the most recent version:
- your version: {$myVersion}
- latest version: {$latestVersion}</comment>
EOT
                );
            }
            return 0;
        }
        $pharContents = $this->fetchPharContents();
        $this->updateMyPhar($pharContents);
        if (!$this->output->isQuiet()) {
            $this->output->writeln(
<<<EOT
<info>The application has been upgraded from version {$myVersion} to {$latestVersion}</info>
EOT
            );
        }
        return 0;
    }

    private function getPharPath(): string
    {
        return Phar::running(false);
    }

    /**
     * @throws \RuntimeException
     */
    private function checkRequirements(): void
    {
        $pharPath = $this->getPharPath();
        if (!is_writeable($pharPath)) {
            throw new RuntimeException("The file {$pharPath} can't be written: you may need to run this command as root.");
        }
    }

    private function getMyVersion(): string
    {
        return $this->console->getVersion();
    }

    /**
     * @throws \RuntimeException
     */
    private function getMostRecentAvailableVersion(): string
    {
        return trim($this->download('version.txt'));
    }

    /**
     * @throws \RuntimeException
     */
    private function fetchPharContents(): string
    {
        $expectedSha384 = trim($this->download('concrete.sig'));
        $phar = $this->download('concrete.phar');
        if ($this->output->isVerbose()) {
            $this->output->write('Checking signature of the PHAR file... ');
        }
        $actualSha384 = hash('sha384', $phar);
        if ($expectedSha384 !== $actualSha384) {
            throw new RuntimeException(
<<<EOT
The downloaded phar file is corrupt!
Expected SHA-384: {$expectedSha384}
Actual SHA-384  : {$actualSha384}
EOT
            );
        }
        if ($this->output->isVerbose()) {
            $this->output->outputDone("passed.\n");
        }

        return $phar;
    }

    /**
     * @throws \RuntimeException
     */
    private function download(string $file): string
    {
        if ($this->output->isVerbose()) {
            $this->output->write("Downloading {$file}... ");
        }
        $url = sprintf(self::LASTESTRELEASE_PATTERN, $file);
        $context = stream_context_create([
            'http' => [
                'follow_location' => 1,
                'ignore_errors' => false,
            ],
        ]);
        $error = 'Unknown error';
        set_error_handler(static function(int $errno, string $errstr) use (&$error): void {
            $error = trim($errstr);
            if ($error === '') {
                $error = "Unknown error (code: {$errno})";
            }
        }, -1);
        $result = file_get_contents($url, false, $context);
        restore_error_handler();
        if ($result === false) {
            throw new RuntimeException("Failed to download {$url}:\n{$error}");
        }
        if ($this->output->isVerbose()) {
            $length = strlen($result);
            $this->output->outputDone("done ({$length} bytes).\n");
        }

        return $result;
    }

    /**
     * @throws \RuntimeException
     */
    private function updateMyPhar(string &$pharContents): void
    {
        if ($this->output->isVerbose()) {
            $this->output->write("Updating PHAR... ");
        }
        $pharPath = $this->getPharPath();
        set_error_handler(static function(): void {}, -1);
        $pharPermissions = fileperms($pharPath);
        restore_error_handler();
        $error = 'Unknown error';
        set_error_handler(static function(int $errno, string $errstr) use (&$error): void {
            $error = trim($errstr);
            if ($error === '') {
                $error = "Unknown error (code: {$errno})";
            }
        }, -1);
        $savedBytes = file_put_contents($pharPath, $pharContents);
        restore_error_handler();
        if ($savedBytes === false) {
            throw new RuntimeException("Failed to set the new PHAR contents:\n{$error}");
        }
        if ($pharPermissions !== false) {
            set_error_handler(static function(): void {}, -1);
            chmod($pharPath, $pharPermissions);
            restore_error_handler();
        }
        if ($this->output->isVerbose()) {
            $this->output->outputDone("done.\n");
        }
    }
}
