<?php

declare(strict_types=1);

namespace Concrete\Console\Installation;

use Composer\Semver\Comparator;
use InvalidArgumentException;

/**
 * @psalm-immutable
 */
class Version
{
    public const VERSION_5 = '5.5';
    public const VERSION_6 = '5.6';
    public const VERSION_7 = '5.7';
    public const VERSION_8 = '8';
    public const VERSION_9 = '9';
    public const VERSION_10 = '10';

    /**
     * @var string
     */
    protected $versionString = '';

    protected function __construct(string $version)
    {
        $this->versionString = $version;
    }

    public function getVersion(): string
    {
        return $this->versionString;
    }

    public static function fromVersionString(string $version): Version
    {
        $version = self::normalizeVersionString($version);
        return new self($version);
    }

    /**
     * Normalize a given version into something that works well with the comparator.
     *
     * @psalm-pure
     */
    public static function normalizeVersionString(
        string $version,
        int $segments = 5,
        bool $validate = true,
        bool $keepNoun = true
    ): string {
        $version = explode('.', trim($version));
        $finalSegment = array_pop($version);
        $withoutNoun = (string)intval($finalSegment);
        $noun = substr($finalSegment, strlen($withoutNoun));
        $version[] = $withoutNoun;

        if ($validate && count($version) > $segments) {
            throw new InvalidArgumentException('Invalid version number provided, too many version segments.');
        }

        if (count($version) > $segments) {
            // Delete segments
            $version = array_slice($version, 0, $segments);
        } else {
            // Add segments
            $version = array_pad($version, $segments, '0');
        }

        return implode('.', $version) . ($keepNoun ? $noun : '');
    }

    /**
     * @psalm-return '5.5'|'5.6'|'5.7'|'8'|'9'|'10'
     */
    public function getMajorVersion(): string
    {
        $version = $this->getVersion();
        if (substr($version, 0, 1) === '5') {
            $version = self::normalizeVersionString($version, 2, false, false);
        } else {
            $version = self::normalizeVersionString($version, 1, false, false);
        }

        /** @var '5.6'|'5.7'|'8'|'9' $version */
        return $version;
    }

    /**
     * Evaluates the expression: $version1 > $version2.
     */
    public function greaterThan(string $version): bool
    {
        return Comparator::greaterThan($this->versionString, self::normalizeVersionString($version));
    }

    /**
     * Evaluates the expression: $version1 >= $version2.
     */
    public function greaterThanOrEqualTo(string $version): bool
    {
        return Comparator::greaterThanOrEqualTo($this->versionString, self::normalizeVersionString($version));
    }

    /**
     * Evaluates the expression: $version1 < $version2.
     */
    public function lessThan(string $version): bool
    {
        /** @psalm-suppress */
        return Comparator::lessThan($this->versionString, self::normalizeVersionString($version));
    }

    /**
     * Evaluates the expression: $version1 <= $version2.
     */
    public function lessThanOrEqualTo(string $version): bool
    {
        return Comparator::lessThanOrEqualTo($this->versionString, self::normalizeVersionString($version));
    }

    /**
     * Evaluates the expression: $version1 == $version2.
     */
    public function equalTo(string $version): bool
    {
        return Comparator::equalTo($this->versionString, self::normalizeVersionString($version));
    }

    /**
     * Evaluates the expression: $version1 != $version2.
     */
    public function notEqualTo(string $version): bool
    {
        return Comparator::notEqualTo($this->versionString, self::normalizeVersionString($version));
    }
}
