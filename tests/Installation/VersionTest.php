<?php

namespace Concrete\Console\Installation;

use Concrete\Console\TestCase;

class VersionTest extends TestCase
{

    /**
     * @dataProvider versionComparisons
     *
     * @param string $version
     * @param string $compare
     * @param bool[] $expect
     */
    public function testVersionComparisons(string $version, string $compare, array $expect)
    {
        $errorString = 'failed with version "' . $version . '" compared to "' . $compare . '"';
        $version = Version::fromVersionString($version);

        $methods = [
            'greaterThan',
            'greaterThanOrEqualTo',
            'lessThan',
            'lessThanOrEqualTo',
            'equalTo',
            'notEqualTo',
        ];

        $methods = array_combine($methods, $expect);
        foreach ($methods as $method => $expected) {
            $this->assertEquals($expected, $version->{$method}($compare), "{$method} {$errorString}");
        }
    }

    public function versionComparisons(): array
    {
        $greater = [true, true, false, false, false, true];
        $equal = [false, true, false, true, true, false];
        $less = [false, false, true, true, false, true];

        return [
            // Long version chains vs short version numbers
            ['5.7.0', '5.7', $equal],
            ['5.7.0.0.1', '5.7', $greater],
            ['5.6.0.0.1', '5.7', $less],
            ['5.7.5.2', '5', $greater],
            ['5.7.5.2', '5.7.5.2', $equal],
            ['5.7.5.2', '5.7.5.3', $less],

            // Broad spectrum
            ['5.6', '9', $less],
            ['9', '5.6', $greater],

            // Versions with nouns
            ['8.5.0RC1', '8.5.0', $less],
            ['8.5.0RC1', '8.5.0RC2', $less],
            ['8.5.0RC3', '8.5.0RC2', $greater],
            ['8.5.0-alpha', '8.5.0-beta', $less],
            ['8.5.1-alpha', '8.5.0', $greater],
            ['5.5.2.2a1', '5.5.2.2', $less],
            ['5.5.2.2a1', '5.5.2.1', $greater],
        ];
    }

    /**
     * @dataProvider majorVersions
     * @param string $version
     * @param string $expected
     */
    public function testMajorVersion(string $version, string $expected)
    {
        $version = Version::fromVersionString($version);
        $this->assertEquals($expected, $version->getMajorVersion());
    }

    public function majorVersions(): array
    {
        return [
            ['5.5.2.2a1', '5.5'],
            ['5.6.5.5.1RC2', '5.6'],
            ['5.7.5.5.1', '5.7'],
            ['8.5.5.1', '8'],
            ['9', '9'],
            ['9.0.0.0.0beta', '9'],
            ['10.1.1', '10'],
        ];
    }
}
