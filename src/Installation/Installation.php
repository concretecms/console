<?php

namespace Concrete\Console\Installation;

class Installation
{

    /**
     * @var string
     */
    protected $path;

    /**
     * Installation constructor.
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    

}