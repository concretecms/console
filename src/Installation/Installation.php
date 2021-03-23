<?php

namespace Concrete\Console\Installation;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

/**
 * @psalm-immutable
 */
class Installation
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var Version
     */
    protected $version;

    /**
     * Installation constructor.
     * @param string $path
     * @param Version $version
     */
    public function __construct(string $path, Version $version)
    {
        $this->path = $path;
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return Version
     */
    public function getVersion(): Version
    {
        return $this->version;
    }

    public function getFilesystem(): Filesystem
    {
        return new Filesystem(new Local($this->getPath()));
    }
}
