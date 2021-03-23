<?php
namespace Concrete\Console\Command\Database;

use Concrete\Console\Application;
use Concrete\Console\Command\Command;
use League\Container\Container;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

class DumpCommand extends Command
{

    public function __invoke(string $file, Input $input)
    {
        $app = $this->getApplication();
        $config = $app->make('config')->get('database');
        $connection = $config['default-connection'];

        foreach($config['connections'] as $identifier => $connectionRow) {
            if ($identifier == $connection) {
                if (!$file) {
                    $date = new \DateTime();
                    $file = 'db_' . $connectionRow['database'] . '_' . $date->format('Y-m-d-H-i-s') . '.sql';
                }

                // @todo add port? Do we really not have the option in the config?
                $mysqldump = sprintf(
                    "mysqldump --host='%s' --port='%s' --user='%s' --password='%s' '%s' > '%s'",
                    $connectionRow['server'],
                    isset($connectionRow['port']) ? $connectionRow['port'] : 3306,
                    $connectionRow['username'],
                    $connectionRow['password'],
                    $connectionRow['database'],
                    $file
                );

                $outputFile = $file;
                $this->writeln(sprintf('Exporting database: %s', $connectionRow['database']));

                $process = new Process($mysqldump);
                $process->setTimeout(null);
                $process->run();
                if ($process->isSuccessful()) {
                    if ($input->getOption('gz')) {
                        $outputFile = $file . '.gz';
                        $this->writeln('Compressing file with gzip...');
                        $process = new Process(['gzip', $file]);
                        $process->setTimeout(null);
                        $process->run();
                    }

                    $this->writeln(sprintf('Database backed up to file: %s', $outputFile));
                } else {
                    $this->writeln('<error>' . $process->getErrorOutput() . '</error>');
                }

                return 0;
            }
        }

        $this->writeln('<error>Unable to locate default Concrete database connection.</error>');
        return 1;
    }

    public static function register(Container $container, Application $console): void
    {
        $console->command('database:dump [file] [-z|--gz]', self::class)
            ->descriptions('Dumps the Concrete database to a file', [
                'file' => 'Filename for the dump file',
                '--gz' => 'Flag to gzip',
            ]);
    }
}
