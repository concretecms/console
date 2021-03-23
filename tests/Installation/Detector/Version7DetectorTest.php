<?php

namespace Concrete\Console\Installation\Detector;

use Concrete\Console\TestCase;

class Version7DetectorTest extends AbstractVersionDetectorTest
{

    protected $class = Version7Detector::class;
    protected $paths = [
        'valid' => [
            ['tests/fixtures/adapter/v7', '5.7.5.13'],
            ['tests/fixtures/adapter/v8', '8.6.0a2'],
        ],
        'invalid' => [
            ['tests/fixtures/adapter/v6/NotInstalled'],
            ['tests/fixtures/adapter/v6/Installed'],
        ]
    ];
}
