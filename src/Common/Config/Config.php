<?php
namespace Common\Config;

use Interop\Container\ContainerInterface;

class Config
{
    protected $config = [];
    public function __construct($config)
    {
        $this->config = $config;
    }
    public function get($route)
    {
        $routes = explode('.', $route);
        $endPath = '';
        $config = $this->config;
        foreach ($routes as $item) {
            $endPath .= $item . '.';
            if (!isset($config[$item])) {
                throw new \RuntimeException('' . $endPath);
            }
            $config = $config[$item];
        }

        return $config;
    }
}