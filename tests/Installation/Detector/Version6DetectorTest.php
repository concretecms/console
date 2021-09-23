<?php

declare(strict_types=1);

namespace Concrete\Console\Installation\Detector;

class Version6DetectorTest extends AbstractVersionDetectorTest
{

    protected $class = Version6Detector::class;
    protected $paths = [
        'valid' => [
            ['tests/fixtures/adapter/v6/Installed', '5.6.4'],
        ],
        'invalid' => [
            ['tests/fixtures/adapter/v6/NotInstalled'],
            ['tests/fixtures/adapter/v7'],
            ['tests/fixtures/adapter/v8'],
        ]
    ];
}
