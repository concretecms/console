<?php

namespace Concrete\Core\Foundation\Runtime {

    class Runtime
    {
        private $app;

        public function __construct($app)
        {
            $this->app = $app;
        }

        public function boot()
        {
            $this->app->booted = true;
        }
    }
}

namespace Concrete\Core\Application {

    class Application
    {
        public $booted = false;

        public function getRuntime()
        {
            return new \Concrete\Core\Foundation\Runtime\Runtime($this);
        }
    }

    return new Application();
}
