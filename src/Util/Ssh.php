<?php

declare(strict_types=1);

namespace Concrete\Console\Util;

use Closure;
use Exception;
use Symfony\Component\Process\Process;

/**
 * @psalm-immutable
 */
final class Ssh
{

    /** @var string */
    protected $sshExecutable = 'ssh';

    /** @var string */
    protected $scpExecutable = 'scp';

    /** @var string */
    protected $user;

    /** @var string */
    protected $host;

    /** @var string */
    protected $pathToPrivateKey = '';

    /** @var int|null */
    protected $port;

    /** @var bool */
    protected $enableStrictHostChecking = true;

    /** @var bool */
    protected $quietMode = false;

    /** @var bool */
    protected $enablePasswordAuthentication = true;

    /** @var Closure(Process): void */
    protected $processConfigurationClosure;

    /** @var callable(string, string): void */
    protected $onOutput;

    public function __construct(string $user, string $host, int $port = null)
    {
        $this->user = $user;
        $this->host = $host;
        $this->port = $port;
        $this->processConfigurationClosure = function (Process $process): void {
        };
        $this->onOutput = function (string $type, string $line): void {
        };
    }

    /**
     * @param string $user
     * @param string $host
     * @param int|null $port
     * @return static
     */
    public static function create(string $user, string $host, int $port = null): self
    {
        return new Ssh($user, $host, $port);
    }

    public function withPrivateKey(string $pathToPrivateKey): self
    {
        $self = clone $this;
        $self->pathToPrivateKey = $pathToPrivateKey;

        return $self;
    }

    /**
     * @param int $port
     * @return $this
     * @throws Exception If the port is negative
     */
    public function withPort(int $port): self
    {
        if ($port < 0) {
            throw new Exception('Port must be a positive integer.');
        }
        $self = clone $this;
        $self->port = $port;

        return $self;
    }

    public function withProcessConfiguration(Closure $processConfigurationClosure): self
    {
        $self = clone $this;
        $self->processConfigurationClosure = $processConfigurationClosure;

        return $self;
    }

    public function withOnOutput(Closure $onOutput): self
    {
        $self = clone $this;
        $self->onOutput = $onOutput;

        return $self;
    }

    public function withStrictHostKeyChecking(): self
    {
        $self = clone $this;
        $self->enableStrictHostChecking = true;

        return $self;
    }

    public function withoutStrictHostKeyChecking(): self
    {
        $self = clone $this;
        $self->enableStrictHostChecking = false;

        return $self;
    }

    public function withQuietMode(): self
    {
        $self = clone $this;
        $self->quietMode = true;

        return $self;
    }

    public function withoutQuietMode(): self
    {
        $self = clone $this;
        $self->quietMode = false;

        return $self;
    }

    public function withoutPasswordAuthentication(): self
    {
        $self = clone $this;
        $self->enablePasswordAuthentication = false;

        return $self;
    }

    public function withPasswordAuthentication(): self
    {
        $self = clone $this;
        $self->enablePasswordAuthentication = true;

        return $self;
    }

    /**
     * @param string|array $command
     * @return string
     */
    public function getExecuteCommand($command): string
    {
        $commands = $this->wrapArray($command);
        $extraOptions = $this->getExtraSshOptions();
        $commandString = implode(PHP_EOL, $commands);
        $delimiter = 'EOF-SPATIE-SSH';
        $target = $this->getTarget();

        return "{$this->sshExecutable} {$extraOptions} {$target} 'bash -se' << \\$delimiter" . PHP_EOL
            . $commandString . PHP_EOL
            . $delimiter;
    }

    /**
     * @param string|array $command
     *
     * @return Process
     */
    public function execute($command): Process
    {
        $sshCommand = $this->getExecuteCommand($command);

        return $this->run($sshCommand);
    }

    /**
     * @param string|array $command
     *
     * @return Process
     */
    public function executeAsync($command): Process
    {
        $sshCommand = $this->getExecuteCommand($command);

        return $this->run($sshCommand, 'start');
    }

    /**
     * @param string $sourcePath
     * @param string $destinationPath
     * @return string
     */
    public function getDownloadCommand(string $sourcePath, string $destinationPath): string
    {
        return "{$this->scpExecutable} {$this->getExtraScpOptions()} {$this->getTarget()}:$sourcePath $destinationPath";
    }

    public function download(string $sourcePath, string $destinationPath): Process
    {
        $downloadCommand = $this->getDownloadCommand($sourcePath, $destinationPath);

        return $this->run($downloadCommand);
    }

    public function getUploadCommand(string $sourcePath, string $destinationPath): string
    {
        return "{$this->scpExecutable} {$this->getExtraScpOptions()} $sourcePath {$this->getTarget()}:$destinationPath";
    }

    public function upload(string $sourcePath, string $destinationPath): Process
    {
        $uploadCommand = $this->getUploadCommand($sourcePath, $destinationPath);

        return $this->run($uploadCommand);
    }

    protected function getExtraSshOptions(): string
    {
        $extraOptions = $this->getExtraOptions();

        if (!is_null($this->port)) {
            $extraOptions[] = "-p {$this->port}";
        }

        return implode(' ', $extraOptions);
    }

    /**
     * @return string
     */
    protected function getExtraScpOptions(): string
    {
        $extraOptions = $this->getExtraOptions();

        $extraOptions[] = '-r';

        if (!is_null($this->port)) {
            $extraOptions[] = "-P {$this->port}";
        }

        return implode(' ', $extraOptions);
    }

    /**
     * @return array
     */
    private function getExtraOptions(): array
    {
        $extraOptions = [];

        if ($this->pathToPrivateKey) {
            $extraOptions[] = "-i {$this->pathToPrivateKey}";
        }

        if (!$this->enableStrictHostChecking) {
            $extraOptions[] = '-o StrictHostKeyChecking=no';
            $extraOptions[] = '-o UserKnownHostsFile=/dev/null';
        }

        if (!$this->enablePasswordAuthentication) {
            $extraOptions[] = '-o PasswordAuthentication=no';
        }

        if ($this->quietMode) {
            $extraOptions[] = '-q';
        }

        return $extraOptions;
    }

    /**
     * @param array|string $arrayOrString
     * @return array
     */
    protected function wrapArray($arrayOrString): array
    {
        return (array)$arrayOrString;
    }

    public function run(string $command, string $method = 'run'): Process
    {
        $process = \process($command, null, null, null, 0);
        ($this->processConfigurationClosure)($process);
        $process->{$method}($this->onOutput);

        return $process;
    }

    protected function getTarget(): string
    {
        return "{$this->user}@{$this->host}";
    }
}
