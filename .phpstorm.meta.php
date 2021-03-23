<?php
namespace PHPSTORM_META;

use Concrete\Core\Application\Application;
use Psr\Container\ContainerInterface;

override(ContainerInterface::get(0), map([
  '' => '@',
]));
override(Application::make(0), map([
  '' => '@',
]));
