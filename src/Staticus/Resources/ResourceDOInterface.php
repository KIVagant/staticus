<?php
namespace Staticus\Resources;

/**
 * Default Recourse Domain Object Interface
 * @package Staticus\Resources\File
 */
interface ResourceDOInterface
{
    const DEFAULT_VARIANT = 'def';
    const DEFAULT_VERSION = 0;
    const NAMESPACE_REG_SYMBOLS = '\w\d\-\'_\/';
    const NAME_REG_SYMBOLS = '\w\d\p{L} \-\.\'_';

    /**
     * @return string
     */
    public function getMimeType();
    /**
     * @return ResourceDOInterface
     */
    public function reset();

    /**
     * @return mixed
     */
    public function getUuid();

    /**
     * @return string
     */
    public function getNamespace();

    /**
     * @param string $namespace
     * @return ResourceDOInterface
     */
    public function setNamespace($namespace);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return ResourceDOInterface
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getNameAlternative();

    /**
     * @param string $nameAlternative
     * @return ResourceDOInterface
     */
    public function setNameAlternative($nameAlternative);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     * @return ResourceDOInterface
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getVariant();

    /**
     * @param string $variant
     * @return ResourceDOInterface
     */
    public function setVariant($variant = 'default');

    /**
     * @return int
     */
    public function getVersion();

    /**
     * @param int $version
     * @return ResourceDOInterface
     */
    public function setVersion($version = 0);

    /**
     * @return string
     */
    public function getAuthor();

    /**
     * @param string $author
     * @return ResourceDOInterface
     */
    public function setAuthor($author);

    /**
     * @return string
     */
    public function getFilePath();

    /**
     * @return string
     */
    public function generateFilePath();

    /**
     * @return mixed
     */
    public function getBaseDirectory();

    /**
     * @param string $dir
     * @return ResourceDOInterface
     */
    public function setBaseDirectory($dir);

    /**
     * @return boolean
     */
    public function isNew();

    /**
     * @param boolean $new
     * @return ResourceDOAbstract
     */
    public function setNew($new = false);

    /**
     * @return boolean
     */
    public function isRecreate();

    /**
     * @param boolean $recreate
     * @return ResourceDOInterface
     */
    public function setRecreate($recreate = false);
}