<?php

declare(strict_types=1);

namespace Concrete\Console\Command\Backup;

use Concrete\Console\Application;
use Concrete\Console\Command\Command;
use Concrete\Console\Installation\ManifestFactory;
use League\Container\Container;
use PharData;
use PharFileInfo;
use Symfony\Component\Console\Input\InputInterface;

class InspectCommand extends Command
{

    public function __invoke(string $backupFile, InputInterface $input, ManifestFactory $factory): int
    {
        if (!file_exists($backupFile)) {
            $this->output->error('Backup file not found.');
            return 111;
        }

        $manifest = $factory->forBackup($backupFile);

        if (!$manifest) {
            $this->output->error('Unable to load manifest from backup file.');
            return 112;
        }

        if ($path = $input->getOption('ls')) {
            /** @var string $path */
            return $this->list($path, $backupFile);
        }

        if ($input->getOption('manifest-only')) {
            $this->output->writeln(json_encode($manifest, JSON_PRETTY_PRINT));
            return 0;
        }
        $testAdjective = function (string $affirmative, string $negative): callable {
            return function (bool $test) use ($affirmative, $negative): string {
                return $test ? '<fg=cyan>' . $affirmative . '</>' : '<fg=red>' . $negative . '</>';
            };
        };

        $included = $testAdjective('Included', 'Excluded');
        $installed = $testAdjective('Installed', 'Not Installed');

        $this->output->table(['basics'], [
            ['Version', $manifest->getVersion()],
            ['Core', $included($manifest->includesCore())],
            ['Index', $included($manifest->includesIndex())],
        ]);

        $packages = [];
        foreach ($manifest->getPackages() as $package) {
            $packages[] = [
                dot_get($package, 'handle'),
                $installed(!!dot_get($package, 'installed')),
                $included(!!dot_get($package, 'included'))
            ];
        }
        $this->output->table(['Packages'], $packages);

        $locations = [];
        foreach ($manifest->getStorageLocations() as $location) {
            $locations[] = [
                dot_get($location, 'name'),
                dot_get($location, 'id'),
                number_format($this->fileCount($backupFile, $location['id'])),
                $included(!!dot_get($location, 'included')),
                $testAdjective('Default', '')(!!dot_get($location, 'default')),
            ];
        }

        $keys = ['<th>Storage Locations</th>', '<th>ID</th>', '<th>File count</th>', 'Is Included', 'Is Default'];
        $locations = array_map(function ($columns) use ($keys) {
            return array_combine($keys, $columns);
        }, $locations);

        $this->output->table(['Storage Locations', 'ID', 'File Count'], $locations);
        return 0;
    }

    public static function register(Container $container, Application $console): void
    {
        self::command($console, 'backup:inspect backupfile [-r|--manifest-only] [--ls=]', self::class)
            ->descriptions('Inspects a Concrete installation backup', [
                'backupfile' => 'The path to the backup file to inspect',
                '--manifest-only' => 'Output the raw manifest json, combine with <info>jq</> command.',
                '--ls' => 'List out files at a given path'
            ]);
    }

    protected function fileCount(string $backupFile, int $storageLocationId): int
    {
        $data = new PharData($backupFile . '/storage/' . $storageLocationId);

        /** @psalm-suppress TooManyArguments Psalm has the wrong definition for PharData::count */
        return $data->count(COUNT_RECURSIVE);
    }

    private function list(string $path, string $backupFile): int
    {
        $backup = new PharData($backupFile);
        $path = ltrim($path, '/');
        $headers = [
            'Size', 'Path'
        ];
        $table = [];
        $isFile = false;

        if ($path) {
            if (!$backup->offsetExists($path)) {
                $this->output->error('Cannot access "' . $path . '": No such file or directory');
                return 1;
            }

            /** @var PharFileInfo $backupDir */
            $backupDir = $backup->offsetGet($path);

            if (!$backupDir->isDir()) {
                $table[] = [$backupDir->getSize(), $backupDir->getFilename()];
                $isFile = true;
            } else {
                $backup = new PharData($backupDir->getPathname());
            }
        }

        if (!$isFile) {
            /** @var PharFileInfo $child */
            foreach ($backup as $child) {
                $colors = $child->isDir() ? '<fg=cyan>' : '';
                $close = $child->isDir() ? '/</>' : '';
                $table[] = [
                    $child->isDir() ? '' : $child->getSize(),
                    $colors . ltrim(implode('/', [$path, $child->getFilename()]), '/') . $close
                ];
            }
        }

        $this->output->table($headers, $table);
        return 0;
    }
}
