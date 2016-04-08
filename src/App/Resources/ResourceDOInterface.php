<?php
namespace App\Resources;

/**
 * Default Recourse Domain Object Interface
 * @package App\Resources\File
 */
interface ResourceDOInterface
{
    const DEFAULT_VARIANT = 'def';
    const DEFAULT_VERSION = 0;

    public function reset();

    /**
     * @return mixed
     */
    public function getUuid();
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
     * @return mixed
     */
    public function getBaseDirectory();

    /**
     * @param string $dir
     * @return ResourceDOInterface
     */
    public function setBaseDirectory($dir);
}