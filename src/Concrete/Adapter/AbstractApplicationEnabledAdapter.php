<?php
namespace Concrete\Console\Concrete\Adapter;

use Concrete\Console\Concrete\Connection\ApplicationEnabledConnectionInterface;
use Concrete\Console\Concrete\Connection\ConnectionInterface;
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

        // Setup
        $this->defineConstants($path);
        $this->registerAutoload($path);

        // Get the concrete5 application
        $cms = $this->getApplicationInstance($path);

        // Boot the runtime
        $this->bootApplication($cms);

        return $cms;
    }

    /**
     * @param $path
     */
    protected function defineConstants(string $path): void
    {
        // Define some required constants
        define('DIR_BASE', $path);
        define('C5_ENVIRONMENT_ONLY', true);

        // Load in the rest of them
        require $path . '/concrete/bootstrap/configure.php';
    }

    /**
     * @param $path
     */
    protected function registerAutoload(string $path): void
    {
        // Load in concrete5's autoloader
        require $path . '/concrete/bootstrap/autoload.php';
    }

    /**
     * @param $path
     * @return Application
     */
    protected function getApplicationInstance(string $path): Application
    {
        return require $path . '/concrete/bootstrap/start.php';
    }

    /**
     * @param $cms
     */
    protected function bootApplication(Application $cms): void
    {
        if (method_exists($cms, 'getRuntime')) {
            $runtime = $cms->getRuntime();
            $runtime->boot();
        }
    }
}
