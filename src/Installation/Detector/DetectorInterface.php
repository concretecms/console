<?php

declare(strict_types=1);

namespace Concrete\Console\Installation\Detector;

use Concrete\Console\Installation\DatabaseCredentials;
use Concrete\Console\Installation\Version;

interface DetectorInterface
{
    public function versionAtPath(string $path): ?Version;
}
