<?php

declare(strict_types=1);

namespace Concrete\Console\Installation;

use Concrete\Console\TestCase;

class ManifestTest extends TestCase
{

    public function testManuallyBuilt(): void
    {
        $manifest = (new Manifest())

            // Set all values from complete.json
            ->setDateCreated(\DateTime::createFromFormat(Manifest::DATE_FORMAT, '2021-03-10T20:40:50+0000'))
            ->setSiteName('concrete5 Site')
            ->setUrl('https://some.url')
            ->setInstallationPath('/home/foo/concrete5')
            ->setHostName('foo.local')
            ->setVersion('1.0')
            ->setDatabase('db.sql')
            ->setIncludeCore(true)
            ->setIncludeIndex(true)

            // Add application stuff
            ->addApplicationItems([
                'views',
                'tools',
                'themes',
                'src',
                'single_pages',
                'page_templates',
                'mail',
                'languages',
                'attributes',
                'authentication',
                'blocks',
                'bootstrap',
                'config',
                'controllers',
                'elements',
                'jobs',
            ])

            // Add storage locations
            ->addStorageLocation(1, 'red', true, true)
            ->addStorageLocation(2, 'blue', true, false)
            ->addStorageLocation(3, 'green', false, true)
            ->addStorageLocation(4, 'yellow', false, false)

            // Add Packages
            ->addPackage('foo', true, true)
            ->addPackage('baz', false, true)
            ->addPackage('zab', true, false)
            ->addPackage('bar', false, false)
        ;

        $contents = json_decode(file_get_contents(__DIR__ . '/../fixtures/manifest/complete.json'), true);
        $this->assertSame($contents, $manifest->jsonSerialize());
    }

    /**
     * @dataProvider filesToTest
     */
    public function testCompleteJson(string $file): void
    {
        $contents = json_decode(file_get_contents(__DIR__ . '/../fixtures/manifest/' . $file), true);
        $manifest = Manifest::jsonDeserialize($contents);

        // Validate the manifest against expected values
        $this->assertEquals($contents, $manifest->jsonSerialize());
    }

    public function filesToTest(): iterable
    {
        yield ['complete.json'];
        yield ['opposite.json'];
    }
}
