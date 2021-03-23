<?php


namespace Concrete\Console\Concrete;


use Concrete\Console\Util\Ssh;
use Symfony\Component\Process\Process;

class InstanceConfig
{

    /** @var string */
    protected $host;
    /** @var string */
    protected $user;
    /** @var string */
    protected $path;
    /** @var string */
    protected $sshKey;
    /** @var string */
    protected $sshKeypassword;
    /** @var string */
    protected $port;
    /** @var string */
    protected $consolePath;

    /**
     * @param ?callable(Ssh): Ssh $sshConfig
     * @return Ssh
     */
    public function ssh($sshConfig = null): Ssh
    {
        $ssh = new Ssh($this->user, $this->host, (int) $this->port);
        return $sshConfig ? $sshConfig($ssh) : $ssh;
    }

    public function executeConsole(string $command, callable $sshConfig = null): Process
    {
        return $this->execute($this->consolePath . ' ' . $command, $sshConfig);
    }

    public function executeAsyncConsole(string $command, callable $sshConfig = null): Process
    {
        return $this->executeAsync($this->consolePath . ' ' . $command, $sshConfig);
    }

    public function downloadFile(string $remotePath, string $localPath, callable $sshConfig = null): Process
    {
        if ($this->host === 'localhost') {
            return $this->ssh($sshConfig)->run("cp '{$remotePath}' '{$localPath}'");
        }

        return $this->ssh($sshConfig)->download($remotePath, $localPath);
    }

    public function execute(string $command, callable $sshConfig = null): Process
    {
        if ($this->host === 'localhost') {
            return $this->ssh($sshConfig)->run("cd {$this->path} && {$command}");
        }

        return $this->ssh($sshConfig)->execute('cd ' . $this->path . "\n" . $command);
    }

    public function executeAsync(string $command, callable $sshConfig = null): Process
    {
        if ($this->host === 'localhost') {
            return $this->ssh($sshConfig)->run("cd {$this->path} && {$command}", 'start');
        }

        return $this->ssh($sshConfig)->executeAsync('cd ' . $this->path . "\n" . $command);
    }

    /**
     * @param array $instances
     * @return array<string, InstanceConfig>
     */
    public static function fromJson(array $instances): array
    {
        $result = [];
        foreach ($instances as $handle => $instance) {
            $config = new InstanceConfig();

            $config->host = dot_get($instance, 'host', '');
            $config->user = dot_get($instance, 'user', '');
            $config->path = dot_get($instance, 'path', '');
            $config->consolePath = dot_get($instance, 'consolePath', 'concrete');
            $config->port = dot_get($instance, 'port', '22');
            $config->sshKey = dot_get($instance, 'sshKey', '');
            $config->sshKeypassword = dot_get($instance, 'sshKeyPassword', '');

            $result[$handle] = $config;
        }

        return $result;
    }

}
