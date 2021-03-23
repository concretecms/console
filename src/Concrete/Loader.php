<?php


namespace Concrete\Console\Concrete;

/**
 * Loader shim for legacy concrete5 instances. Allows accessing `\Loader` stuff in a testable way
 */
class Loader
{

    /**
     * @param string $method
     * @param mixed ...$args
     * @return mixed
     */
    protected function callLoader(string $method, ...$args)
    {
        return \Loader::{$method}(...$args);
    }

    /**
     * @param string $library
     * @param string|null $packageHandle
     * @return mixed
     */
    public function library(string $library, string $packageHandle = null)
    {
        return $this->callLoader('library', $library, $packageHandle);
    }

    /**
     * @param string $model
     * @param string|null $packageHandle
     * @return mixed
     */
    public function model(string $model, string $packageHandle = null)
    {
        return $this->callLoader('model', $model, $packageHandle);
    }

    /**
     * @param string $file
     * @param string $packageHandle
     * @param array|null $args
     * @return mixed
     */
    public function packageElement(string $file, string $packageHandle, array $args = null)
    {
        return $this->callLoader('model', $file, $packageHandle, $args);
    }

    /**
     * @param string $file
     * @param array|null $args
     * @return mixed
     */
    public function element(string $file, array $args = null)
    {
        return $this->callLoader('model', $file, $args);
    }

    /**
     * @param string $file
     * @param array|null $args
     * @param string|null $packageHandle
     * @return mixed
     */
    public function tool(string $file, array $args = null, string $packageHandle = null)
    {
        return $this->callLoader('model', $file, $args, $packageHandle);
    }

    /**
     * @param string $block
     * @return mixed
     */
    public function block(string $block)
    {
        return $this->callLoader('model', $block);
    }

    /**
     * @return mixed
     */
    public function database()
    {
        return $this->callLoader('database');
    }

    /**
     * @return mixed
     */
    public function db()
    {
        return $this->callLoader('db');
    }

    /**
     * @param string $file
     * @param string|null $pkgHandle
     * @return mixed
     */
    public function helper(string $file, string $pkgHandle = null)
    {
        // Shim in false for pkgHandle default value
        if ($pkgHandle === null) {
            $pkgHandle = false;
        }

        return $this->callLoader('helper', $file, $pkgHandle);
    }

    /**
     * @param string $package
     * @return mixed
     */
    public function package(string $package)
    {
        return $this->callLoader('package', $package);
    }

    /**
     * @param string $package
     * @return mixed
     */
    public function startingPointPackage(string $package)
    {
        return $this->callLoader('startingPointPackage', $package);
    }

    /**
     * @param string $collectionType
     * @return mixed
     */
    public function pageTypeControllerPath(string $collectionType)
    {
        return $this->callLoader('pageTypeControllerPath', $collectionType);
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function controller(string $path)
    {
        return $this->callLoader('controller', $path);
    }
}
