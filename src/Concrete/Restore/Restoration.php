<?php

namespace Concrete\Console\Concrete\Restore;

use Concrete\Console\Installation\Installation;
use Concrete\Console\Installation\Manifest;
use PharData;

class Restoration
{

    /** @var string|null */
    protected $extracted = null;

    /** @var PharData */
    protected $backup;

    /** @var Manifest */
    protected $manifest;

    /** @var Installation */
    protected $install;

    /** @var string */
    protected $temp;

    /** @var bool */
    protected $isDryRun;

    /** @var array<string, string> */
    protected $attributes = [];

    public static function forBackup(
        PharData $backup,
        Manifest $manifest,
        Installation $install,
        string $temp,
        bool $dryrun,
        array $attributes = []
    ): Restoration {
        $self = new Restoration();
        $self->backup = $backup;
        $self->manifest = $manifest;
        $self->install = $install;
        $self->temp = $temp;
        $self->isDryRun = $dryrun;
        $self->attributes = $attributes;

        return $self;
    }

    public function hasExtracted(): bool
    {
        return $this->extracted !== null;
    }

    public function extract(): string
    {
        if (!$this->extracted) {
            do {
                $extractDir = $this->tempDir(uniqid('extract'), false);
            } while (file_exists($extractDir));

            $this->getBackup()->extractTo($extractDir);
            $this->extracted = $extractDir;
        }

        return $this->extracted;
    }

    public function tempDir(string $subpath = null, bool $create = true): string
    {
        $temp = rtrim($this->temp, '/');

        if ($subpath) {
            $temp .= '/' . trim($subpath, '/');
            if ($create && !file_exists($temp)) {
                mkdir($temp, 0777, true);
            }
        }

        return $temp;
    }

    public function getBackup(): PharData
    {
        return $this->backup;
    }

    /**
     * @return Installation
     */
    public function getInstallation(): Installation
    {
        return $this->install;
    }

    public function findConcretePath(string $subpath, string $path = null): array
    {
        if (!$path) {
            $path = $this->getInstallation()->getPath();
        }

        $path = rtrim($path, '/');
        $subpath = ltrim($subpath);

        $results = [];
        $check = [
            '',
            'public',
            'www',
            'web',
        ];

        foreach ($check as $expectedPath) {
            $fullPath = implode(
                '/',
                array_filter(
                    [
                        $path,
                        $expectedPath,
                        $subpath
                    ]
                )
            );

            if (file_exists($fullPath)) {
                $results[] = realpath($fullPath);
            }
        }

        return $results;
    }

    /**
     * @return Manifest
     */
    public function getManifest(): Manifest
    {
        return $this->manifest;
    }

    /**
     * @return bool
     */
    public function isDryRun(): bool
    {
        return $this->isDryRun;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
