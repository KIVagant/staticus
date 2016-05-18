<?php
namespace Staticus\Config;

interface ConfigInterface
{
    public function all();
    public function get($route, $default);
    public function has($route);
}