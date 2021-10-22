<?php

declare(strict_types=1);

namespace Concrete\Console\Concrete\Restore\Strategy;

use Concrete\Console\Concrete\Connection\ApplicationEnabledConnectionInterface;
use Concrete\Console\Concrete\Connection\ConnectionAwareInterface;
use Concrete\Console\Concrete\Connection\ConnectionAwareTrait;
use Concrete\Console\Concrete\Restore\Restoration;
use PDO;
use RuntimeException;
use Throwable;

/**
 * @psalm-type DatabaseCredentialsType=array{
 *   server: string,
 *   database: string,
 *   username: string,
 *   password: string,
 *   charset: string,
 *   collation: string,
 *   cert: string,
 * }
 */
class RestoreDatabase extends AbstractOutputtingStrategy implements ConnectionAwareInterface
{
    use ConnectionAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Console\Concrete\Restore\StrategyInterface::restore()
     */
    public function restore(Restoration $job): bool
    {
        $output = $this->getOutputStyle();
        $backup = $job->getBackup();
        $manifest = $job->getManifest();
        $database = $manifest->getDatabase();
        $tempDir = $job->tempDir('database', true);

        if ($database) {
            $output->outputStep('Extracting SQL file');
            $backup->extractTo($tempDir, $database);
            $output->outputDone();
        } else {
            $this->getOutputStyle()->error('Database not included in backup.');
            return false;
        }

        $output->outputStep('Determining database credentials');

        $connection = $this->getDatabaseCredentials($job);
        if (!($sql = $this->testCredentials($connection))) {
            $output->outputDone('Error', '<fg=red>');
            return false;
        }
        $output->outputDone();

        $output->outputStep('Clearing database data');
        $tables = $sql->query('show tables')->fetchAll(PDO::FETCH_COLUMN);
        $sql->exec('set FOREIGN_KEY_CHECKS=0');

        foreach ($tables as $table) {
            if ($job->isDryRun()) {
                continue;
            }

            if ($sql->exec("drop table `{$table}`") === false) {
                throw new RuntimeException(
                    sprintf(
                        'Unable to delete database table "%s" %s',
                        $table,
                        implode(', ', $sql->errorInfo())
                    )
                );
            }
        }
        $sql->exec('set FOREIGN_KEY_CHECKS=1');
        if ($job->isDryRun()) {
            $output->outputDryrun();
        } else {
            $output->outputDone();
        }

        $output->outputStep('Restoring');

        $input = fopen($tempDir . '/' . $database, 'r+');
        $process = process(
            [
                'mysql',
                '-u' . dot_get($connection, 'username', ''),
                '-p' . dot_get($connection, 'password', ''),
                '-h' . dot_get($connection, 'server', ''),
                dot_get($connection, 'database', ''),
            ],
            null,
            null,
            $input,
            0
        );

        if ($job->isDryRun()) {
            $output->outputDryrun();
            $output->outputFinal();
            return true;
        } else {
            $process->start(
                function (string $channel, string $message): void {
                    if ($channel === 'err') {
                        throw new RuntimeException('Failed to restore database: ' . $message);
                    }
                }
            );

            while ($process->isRunning()) {
                sleep(1);
            }

            $output->outputDone($process->isSuccessful() ? 'Done!' : 'Failed.');
            $output->outputFinal();
            return $process->isSuccessful();
        }
    }

    private function getDatabaseCredentials(Restoration $job): array
    {
        $extractConfigCredentials = function (array $config): array {
            $default = dot_get($config, 'default-connection');
            if ($default && $defaultConnection = dot_get($config, 'connections.' . $default)) {
                return [
                    'server' => (string)dot_get($defaultConnection, 'server', ''),
                    'database' => (string)dot_get($defaultConnection, 'database', ''),
                    'username' => (string)dot_get($defaultConnection, 'username', ''),
                    'password' => (string)dot_get($defaultConnection, 'password', ''),
                    'charset' => (string)dot_get($defaultConnection, 'character_set', ''),
                    'collation' => (string)dot_get($defaultConnection, 'collation', ''),
                    'cert' => (string)dot_get($defaultConnection, 'database', ''),
                ];
            }

            throw new \InvalidArgumentException(
                'Invalid configuration provided. Couldn\'t resolve default connection.'
            );
        };

        // First try loading directly using the connection if there is one
        try {
            $connection = $this->getConnection();
            if ($connection && $connection instanceof ApplicationEnabledConnectionInterface) {
                $config = $connection->getApplication()->make('config');
                $result = $config->get('database');

                return $extractConfigCredentials($result);
            }
        } catch (Throwable $e) {
            // Ignore errors
        }

        // Next try calling the built in command line utility
        try {
            $paths = $job->findConcretePath('concrete/bin/concrete5');
            $cliPath = array_shift($paths);
            if ($cliPath) {
                $process = process(
                    [
                        $cliPath,
                        'c5:config',
                        'get',
                        'database'
                    ],
                    $job->getInstallation()->getPath()
                );
                $process->mustRun();

                $result = json_decode($process->getOutput(), true);
                return $extractConfigCredentials($result);
            }
        } catch (Throwable $e) {
            // Ignore errors
        }

        // Finally try loading them directly, this is the least safe because these PHP files may refer to environment
        // or declared variables
        try {
            $path = $job->findConcretePath('application/config/database.php');
            $configPath = array_shift($path);

            if ($configPath) {
                return $extractConfigCredentials(include $configPath);
            }
        } catch (Throwable $e) {
            // Ignore errors
        }

        throw new RuntimeException('Unable to determine database credentials.');
    }

    /**
     * @psalm-assert-if-true DatabaseCredentialsType $connection
     */
    private function testCredentials(?array $connection): ?PDO
    {
        if ($connection) {
            // Test out database credentials
            $sql = new PDO(
                'mysql:host=' . $connection['server'] . ';dbname=' . $connection['database'],
                $connection['username'],
                $connection['password']
            );

            $result = $sql->query('SHOW VARIABLES LIKE "%version%"');
            if ($result->fetchAll()) {
                return $sql;
            }
        }

        return null;
    }
}
