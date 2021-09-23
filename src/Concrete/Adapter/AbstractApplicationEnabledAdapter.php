<?php

declare(strict_types=1);

namespace Concrete\Console\Concrete\Adapter;

use Concrete\Console\Concrete\Connection\ApplicationEnabledConnectionInterface;
use Concrete\Console\Concrete\Connection\ConnectionInterface;
use Concrete\Console\Util\Installation;
use Concrete\Core\Application\Application;

abstract class AbstractApplicationEnabledAdapter implements AdapterInterface
{

    /**
     * Attach to a modern concrete5 site
     * @param string $path
     * @return ApplicationEnabledConnectionInterface $connection
     */
    public function attach(string $path): ConnectionInterface
    {
        $connection = $this->createConnection();
        $connection->connect($this->resolveApplication($path));

        return $connection;
    }

    /**
     * Get the connection to connect with
     *
     * @return ApplicationEnabledConnectionInterface
     */
    abstract protected function createConnection(): ApplicationEnabledConnectionInterface;

    /**
     * Resolve the application object from a concrete5 site
     * @param string $path
     * @return Application
     */
    private function resolveApplication($path): Application
    {
        chdir($path);

        $core = Installation::getConcretePath($path);

        // Setup
        $this->defineConstants($path, $core);
        $this->registerAutoload($path, $core);

        // Get the concrete5 application
        $cms = $this->getApplicationInstance($path, $core);

        // Boot the runtime
        $this->bootApplication($cms);

        return $cms;
    }

    /**
     * @param string $path
     * @param string $core
     */
    protected function defineConstants(string $path, string $core): void
    {
        // Define some required constants
        define('DIR_BASE', dirname($core));
        define('C5_ENVIRONMENT_ONLY', true);

        // Load in the rest of them
        require $core . '/bootstrap/configure.php';
    }

    /**
     * @param string $path
     * @param string $core
     */
    protected function registerAutoload(string $path, string $core): void
    {
        // Load in concrete5's autoloader
        require $core . '/bootstrap/autoload.php';
    }

    /**
     * @param string $path
     * @param string $core
     * @return Application
     */
    protected function getApplicationInstance(string $path, string $core): Application
    {
        return require $core . '/bootstrap/start.php';
    }

    /**
     * @param Application $cms
     */
    protected function bootApplication(Application $cms): void
    {
        if (method_exists($cms, 'getRuntime')) {
            $runtime = $cms->getRuntime();
            $runtime->boot();
        }
    }
}
