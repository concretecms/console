<?php
namespace Concrete\Console\Command\Database;

use Concrete\Console\Command\AbstractInstallationCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Concrete\Core\Support\Facade\Facade;
use Symfony\Component\Process\Process;

class DumpCommand extends AbstractInstallationCommand
{

    protected function configure()
    {
        $this
            ->setName('database:dump')
            ->setDescription('Dumps the Concrete database to a file.')
            ->addArgument('file', InputArgument::OPTIONAL, 'Filename for the dump file.')
            ->addOption(
                'gz',
                null,
                InputOption::VALUE_NONE,
                'Gzip the export'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        $app = Facade::getFacadeApplication();
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
                    3306,
                    $connectionRow['username'],
                    $connectionRow['password'],
                    $connectionRow['database'],
                    $file
                );

                $outputFile = $file;
                $output->writeln(sprintf('Exporting database: %s', $connectionRow['database']));

                $process = Process::fromShellCommandline($mysqldump);
                $process->setTimeout(null);
                $process->run();
                if ($process->isSuccessful()) {
                    if ($input->getOption('gz')) {
                        $outputFile = $file . '.gz';
                        $output->writeln('Compressing file with gzip...');
                        $process = Process::fromShellCommandline(sprintf("gzip '%s'", $file));
                        $process->setTimeout(null);
                        $process->run();
                    }

                    $output->writeln(sprintf('Database backed up to file: %s', $outputFile));
                } else {
                    $output->writeln('<error>' . $process->getErrorOutput() . '</error>');
                }

                return 0;
            }
        }

        $output->writeln('<error>Unable to locate default Concrete database connection.</error>');
        return 1;
    }


}