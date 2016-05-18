<?php
namespace Staticus\FileSystem;
use League\Flysystem\Adapter\Local;
use Staticus\Config\ConfigInterface;


class LocalAdapterFactory
{
    /**
     * @var Config
     */
    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }
    public function __invoke()
    {
        $adapter = new Local($this->config->get('filesystem.adapters.' . Local::class . '.options.root'));

        return $adapter;
    }
}