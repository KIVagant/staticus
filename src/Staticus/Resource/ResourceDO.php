<?php
namespace Staticus\Resource;

/**
 * Domain Object
 * @package Staticus\Resource
 */
class ResourceDO
{
    protected $uuid;
    protected $name;
    protected $nameAlternative;
    protected $type;
    protected $variant;
    protected $version;
    protected $author;
    protected $directory;
    protected $filePath;
    public function reset()
    {
        $this->uuid = '';
        $this->name = '';
        $this->nameAlternative = '';
        $this->type = '';
        $this->variant = 'default';
        $this->version = 0;
        $this->author = '';
        $this->directory = '';
        $this->filePath = '';

        return $this;
    }

    public function __construct()
    {
        $this->reset();
    }
    protected function setUuid()
    {
        $this->uuid = md5($this->name . $this->nameAlternative);
    }
    protected function setFilePath()
    {
        $this->filePath = $this->directory . $this->uuid . '.' . $this->type;
    }

    /**
     * @return mixed
     */
    public function getUuid()
    {
        return $this->uuid;
    }
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return ResourceDO
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->setUuid();
        $this->setFilePath();

        return $this;
    }

    /**
     * @return string
     */
    public function getNameAlternative()
    {
        return $this->nameAlternative;
    }

    /**
     * @param string $nameAlternative
     * @return ResourceDO
     */
    public function setNameAlternative($nameAlternative)
    {
        $this->nameAlternative = $nameAlternative;
        $this->setUuid();
        $this->setFilePath();

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return ResourceDO
     */
    public function setType($type)
    {
        $this->type = $type;
        $this->setFilePath();

        return $this;
    }

    /**
     * @return string
     */
    public function getVariant()
    {
        return $this->variant;
    }

    /**
     * @param string $variant
     * @return ResourceDO
     */
    public function setVariant($variant)
    {
        $this->variant = $variant;
        $this->setFilePath();

        return $this;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param int $version
     * @return ResourceDO
     */
    public function setVersion($version)
    {
        $this->version = $version;
        $this->setFilePath();

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param string $author
     * @return ResourceDO
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @return mixed
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * @param string $dir
     * @return ResourceDO
     */
    public function setDirectory($dir)
    {
        $this->directory = $dir;
        $this->setFilePath();

        return $this;
    }
}