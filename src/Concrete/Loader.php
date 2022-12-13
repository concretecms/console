<?php

declare(strict_types=1);

namespace Concrete\Console\Concrete;

/**
 * Loader shim for legacy concrete5 instances. Allows accessing `\Loader` stuff in a testable way
 */
class Loader
{
    protected function callLoader(string $method, ...$args)
    {
        return \Loader::{$method}(...$args);
    }

    public function library(string $library, string $packageHandle = null)
    {
        return $this->callLoader('library', $library, $packageHandle);
    }

    public function model(string $model, string $packageHandle = null)
    {
        return $this->callLoader('model', $model, $packageHandle);
    }

    public function packageElement(string $file, string $packageHandle, array $args = null)
    {
        return $this->callLoader('model', $file, $packageHandle, $args);
    }

    public function element(string $file, array $args = null)
    {
        return $this->callLoader('model', $file, $args);
    }

    public function tool(string $file, array $args = null, string $packageHandle = null)
    {
        return $this->callLoader('model', $file, $args, $packageHandle);
    }

    public function block(string $block)
    {
        return $this->callLoader('model', $block);
    }

    public function database()
    {
        return $this->callLoader('database');
    }

    public function db()
    {
        return $this->callLoader('db');
    }

    public function helper(string $file, string $pkgHandle = null)
    {
        // Shim in false for pkgHandle default value
        if ($pkgHandle === null) {
            $pkgHandle = false;
        }

        return $this->callLoader('helper', $file, $pkgHandle);
    }

    public function package(string $package)
    {
        return $this->callLoader('package', $package);
    }

    public function startingPointPackage(string $package)
    {
        return $this->callLoader('startingPointPackage', $package);
    }

    public function pageTypeControllerPath(string $collectionType)
    {
        return $this->callLoader('pageTypeControllerPath', $collectionType);
    }

    public function controller(string $path)
    {
        return $this->callLoader('controller', $path);
    }
}
