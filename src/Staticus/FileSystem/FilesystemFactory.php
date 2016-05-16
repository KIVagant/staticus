<?php
namespace Staticus\FileSystem;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;

class FilesystemFactory
{
    protected $adapter;
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }
    public function __invoke()
    {
        return new Filesystem($this->adapter);
    }
}