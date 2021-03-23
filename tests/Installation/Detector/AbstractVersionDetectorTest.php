<?php


namespace Concrete\Console\Installation\Detector;


use Concrete\Console\Installation\Version;
use Concrete\Console\TestCase;

abstract class AbstractVersionDetectorTest extends TestCase
{

    /** @var string The detector class to test with */
    protected $class = '';

    /** @var array{valid: string[], invalid: string[]} */
    protected $paths = [
        'valid' => [],
        'invalid' => [],
    ];

    /**
     * @dataProvider validPaths
     */
    public function testDetectsValidPaths(string $path, string $expected)
    {
        $detector = new $this->class();
        $version = $detector->versionAtPath($path);

        $this->assertNotNull($version, 'Expected version not matched at path ' . $path);
        $this->assertEquals(Version::normalizeVersionString($expected), $version->getVersion(), 'Invalid version matched for path.');
    }

    /**
     * @dataProvider invalidPaths
     */
    public function testSkipsInvalidPaths(string $path)
    {
        $detector = new $this->class();
        $this->assertNull($detector->versionAtPath($path), 'Found version where none should be found.');
    }

    public function validPaths()
    {
        $base = realpath(__DIR__ . '/../../../');
        return array_map(function($path) use ($base) {
            return [$base . '/' . ltrim($path[0], '/'), $path[1]];
        }, $this->paths['valid']);
    }

    public function invalidPaths()
    {
        $base = realpath(__DIR__ . '/../../../');
        return array_map(function($path) use ($base) {
            return [$base . $path[0]];
        }, $this->paths['invalid']);
    }

}
