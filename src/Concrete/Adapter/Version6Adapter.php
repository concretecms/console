<?php

declare(strict_types=1);

namespace Concrete\Console\Concrete\Adapter;

use Concrete\Console\Concrete\Connection\ConnectionInterface;
use Concrete\Console\Concrete\Connection\LegacyConnection;
use Concrete\Console\Util\Installation;
use RuntimeException;

class Version6Adapter implements AdapterInterface
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Console\Concrete\Adapter\AdapterInterface::attach()
     */
    public function attach(string $path): ConnectionInterface
    {
        // Check if headers are sent
        if (headers_sent()) {
            throw new RuntimeException('Loading version 6 after headers are sent is not supported.');
        }

        // Check if we've installed
        if (!file_exists($path . '/config/site.php')) {
            throw new RuntimeException('Connecting to version 6 before installing is not supported.');
        }

        ob_start();
        $result = $this->handleAttaching($path);
        ob_end_clean();
        return $result;
    }

    protected function handleAttaching(string $path): LegacyConnection
    {
        // Change the cwd to the site path
        chdir($path);

        // Define a couple things concrete5 expects
        $core = Installation::getConcretePath($path);
        define('DIR_BASE', dirname($core));
        define('C5_ENVIRONMENT_ONLY', true);

        // Set the error reporting low
        error_reporting(E_ALL | ~E_NOTICE | ~E_WARNING | ~E_STRICT);

        // Add 3rdparty to include path
        set_include_path(get_include_path() . PATH_SEPARATOR . $core . '/libraries/3rdparty');

        // Include Adodb first, not sure why this was needed
        @require_once $core . '/libraries/3rdparty/adodb/adodb.inc.php';

        // Include Loader explicitly
        @require_once $core . '/libraries/loader.php';

        // Load in legacy dispatcher
        @require_once $core . '/dispatcher.php';

        // Adodb Stuff
        $GLOBALS['ADODB_ASSOC_CASE'] = 2;
        $GLOBALS['ADODB_ACTIVE_CACHESECS'] = 300;
        $GLOBALS['ADODB_CACHE_DIR'] = defined('DIR_FILES_CACHE_DB') ? DIR_FILES_CACHE_DB : '';

        return new LegacyConnection();
    }
}
