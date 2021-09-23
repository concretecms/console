<?php

declare(strict_types=1);

namespace Concrete\Console\Installation;

use Concrete\Core\Package\ItemCategory\StorageLocationType;

/**
 * @psalm-type PackageType=array{handle: string, included: bool, installed: bool}
 * @psalm-type StorageLocationType=array{id: int, name: string, default: bool, included: bool}
 * @psalm-type ManifestType=array{
 *   created: string|null,
 *   site: string|null,
 *   url: string|null,
 *   path: string|null,
 *   version: string|null,
 *   host: string|null,
 *   contents: array{
 *     database: string|null,
 *     application: bool,
 *     core: bool,
 *     index: bool,
 *     applicationContents: string[],
 *     packages: PackageType[],
 *     storageLocations: StorageLocationType[]
 *   }
 * }
 * @psalm-immutable
 */
class Manifest implements \JsonSerializable
{
    public const DATE_FORMAT = DATE_ISO8601;

    /** @psalm-var PackageType[] */
    protected $packages = [];

    /** @var string[] */
    protected $applicationContents = [];

    /** @psalm-var StorageLocationType[] */
    protected $storageLocations = [];

    /** @var ?\DateTimeImmutable */
    protected $created = null;

    /** @var string|null */
    protected $database = null;

    /** @var string */
    protected $version = '1.0';

    /** @var string|null */
    protected $installationPath = null;

    /** @var string|null */
    protected $url = null;

    /** @var string|null */
    protected $hostName = null;

    /** @var string|null */
    protected $siteName = null;

    /** @var bool */
    protected $includeCore = false;

    /** @var bool */
    protected $includeIndex = false;

    public function __construct()
    {
        $this->created = new \DateTimeImmutable();
    }

    public function addPackage(string $handle, bool $installed, bool $included): self
    {
        $self = clone $this;
        $self->packages[] = [
            'handle' => $handle,
            'included' => $included,
            'installed' => $installed,
        ];

        return $self;
    }

    /**
     * @param string $handle
     * @return array
     * @psalm-return ?PackageType
     */
    public function getPackage(string $handle): ?array
    {
        foreach ($this->packages as $package) {
            if ($package['handle'] === $handle) {
                return $package;
            }
        }

        return null;
    }

    public function addStorageLocation(int $id, string $name, bool $default, bool $included): self
    {
        $self = clone $this;
        $self->storageLocations[] = [
            'id' => $id,
            'name' => $name,
            'default' => $default,
            'included' => $included,
        ];

        return $self;
    }

    public function addApplicationItem(string $item): self
    {
        $self = $this;
        if (!in_array($item, $this->applicationContents)) {
            $self = clone $this;
            $self->applicationContents[] = $item;
            sort($self->applicationContents);
        }

        return $self;
    }

    public function addApplicationItems(array $items): self
    {
        $self = $this;
        foreach ($items as $item) {
            $self = $self->addApplicationItem($item);
        }

        return $self;
    }

    /**
     * @return array
     * @psalm-return ManifestType
     */
    public function jsonSerialize()
    {
        return [
            'created' => $this->getDateCreated() ? $this->getDateCreated()->format(self::DATE_FORMAT) : null,
            'site' => $this->getSiteName(),
            'url' => $this->getUrl(),
            'path' => $this->getPath(),
            'host' => $this->getHostName(),
            'version' => $this->getVersion(),
            'contents' => [
                'application' => !!$this->getApplicationContents(),
                'applicationContents' => $this->getApplicationContents(),
                'core' => $this->includesCore(),
                'database' => $this->getDatabase(),
                'index' => $this->includesIndex(),
                'packages' => $this->getPackages(),
                'storageLocations' => $this->getStorageLocations(),
            ]
        ];
    }

    /**
     * @psalm-suppress MismatchingDocblockParamType
     * @psalm-param ManifestType $data
     */
    public static function jsonDeserialize(array $data): Manifest
    {
        $createdDate = dot_get($data, 'created');
        $createdDateTime = $createdDate ? \DateTimeImmutable::createFromFormat(self::DATE_FORMAT, $createdDate) : null;

        $self = new Manifest();
        $self->created = $createdDateTime ?: null;
        $self->siteName = dot_get($data, 'site');
        $self->hostName = dot_get($data, 'host');
        $self->url = dot_get($data, 'url');
        $self->installationPath = dot_get($data, 'path');
        $self->version = dot_get($data, 'version');
        $contents = dot_get($data, 'contents', []);
        $self->database = dot_get($contents, 'database');
        $self->includeCore = (bool) dot_get($contents, 'core', false);
        $self->includeIndex = (bool) dot_get($contents, 'index', false);
        $self->applicationContents = (array) dot_get($contents, 'applicationContents');

        // Load in packages
        $packages = (array) dot_get($contents, 'packages');
        foreach ($packages as $package) {
            $package = (array) $package;
            $self = $self->addPackage(
                dot_get($package, 'handle', ''),
                dot_get($package, 'installed', false),
                dot_get($package, 'included', false)
            );
        }

        // Load in storage locations
        $locations = (array) dot_get($contents, 'storageLocations');
        foreach ($locations as $location) {
            $location = (array) $location;
            $self = $self->addStorageLocation(
                dot_get($location, 'id', 0),
                dot_get($location, 'name', ''),
                dot_get($location, 'default', false),
                dot_get($location, 'included', false)
            );
        }

        return $self;
    }


    /**
     * @psalm-return PackageType[]
     */
    public function getPackages(): array
    {
        return $this->packages;
    }

    /**
     * @psalm-return StorageLocationType[]
     */
    public function getStorageLocations(): array
    {
        return $this->storageLocations;
    }

    /**
     * @return string[]
     */
    public function getApplicationContents(): array
    {
        return $this->applicationContents;
    }

    /**
     * @return string|null
     */
    public function getDatabase(): ?string
    {
        return $this->database;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->installationPath;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @return string|null
     */
    public function getHostName(): ?string
    {
        return $this->hostName;
    }

    /**
     * @return string|null
     */
    public function getSiteName(): ?string
    {
        return $this->siteName;
    }

    /**
     * @return bool
     */
    public function includesCore(): bool
    {
        return $this->includeCore;
    }

    /**
     * @return bool
     */
    public function includesIndex(): bool
    {
        return $this->includeIndex;
    }

    /**
     * @param string|null $database
     * @return Manifest
     */
    public function setDatabase(?string $database): Manifest
    {
        $self = clone $this;
        $self->database = $database;
        return $self;
    }

    /**
     * @param string $version
     * @return Manifest
     */
    public function setVersion(string $version): Manifest
    {
        $self = clone $this;
        $self->version = $version;
        return $self;
    }

    /**
     * @param string|null $installationPath
     * @return Manifest
     */
    public function setInstallationPath(?string $installationPath): Manifest
    {
        $self = clone $this;
        $self->installationPath = $installationPath;
        return $self;
    }

    /**
     * @param string|null $url
     * @return Manifest
     */
    public function setUrl(?string $url): Manifest
    {
        $self = clone $this;
        $self->url = $url;
        return $self;
    }

    /**
     * @param string|null $hostName
     * @return Manifest
     */
    public function setHostName(?string $hostName): Manifest
    {
        $self = clone $this;
        $self->hostName = $hostName;
        return $self;
    }

    /**
     * @param string|null $siteName
     * @return Manifest
     */
    public function setSiteName(?string $siteName): Manifest
    {
        $self = clone $this;
        $self->siteName = $siteName;
        return $self;
    }

    /**
     * @param bool $includeCore
     * @return Manifest
     */
    public function setIncludeCore(bool $includeCore): Manifest
    {
        $self = clone $this;
        $self->includeCore = $includeCore;
        return $self;
    }

    /**
     * @param bool $includeIndex
     * @return Manifest
     */
    public function setIncludeIndex(bool $includeIndex): Manifest
    {
        $self = clone $this;
        $self->includeIndex = $includeIndex;
        return $self;
    }

    /**
     * @param \DateTimeInterface|null $created
     */
    public function setDateCreated(?\DateTimeInterface $created): Manifest
    {
        if ($created instanceof \DateTime) {
            $created = \DateTimeImmutable::createFromMutable($created);
        } elseif (!$created instanceof \DateTimeImmutable) {
            throw new \RuntimeException('Unknown datetime type provided.');
        }

        $self = clone $this;
        $self->created = $created;

        return $self;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDateCreated(): ?\DateTimeImmutable
    {
        return $this->created;
    }
}
