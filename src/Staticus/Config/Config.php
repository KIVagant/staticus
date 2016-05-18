<?php
namespace Staticus\Config;

class Config implements ConfigInterface
{
    protected $config = [];
    public function __construct($config)
    {
        $this->config = $config;
    }

    public function all()
    {

        return $this->config;
    }

    public function get($route, $default = null)
    {
        $config = $this->config;
        $routes = explode('.', $route);
        $endPath = '';
        foreach ($routes as $item) {
            $endPath .= $item . '.';
            if (!array_key_exists($item, $config)) {
                if (null !== $default) {

                    return $default;
                }
                throw new \RuntimeException('Unknown config route and no default values: ' . $endPath, __LINE__);
            }
            $config = $config[$item];
        }

        return $config;
    }

    /**
     * @param $route
     * @return bool
     */
    public function has($route)
    {
        $config = $this->config;
        $routes = explode('.', $route);
        $endPath = '';
        foreach ($routes as $item) {
            $endPath .= $item . '.';
            if (!array_key_exists($item, $config)) {

                return false;
            }
            $config = $config[$item];
        }

        return true;
    }
}