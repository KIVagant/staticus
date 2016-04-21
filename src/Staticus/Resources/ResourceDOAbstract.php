<?php
namespace Staticus\Resources;

/**
 * Domain Object
 * @package Staticus\Resources\File
 */
abstract class ResourceDOAbstract implements ResourceDOInterface, \Iterator
{
    const TYPE = '';
    protected $uuid;
    protected $name;
    protected $nameAlternative;
    protected $type = self::TYPE;
    protected $variant;
    protected $version;
    protected $author;

    /**
     * true if resource file is just created (or should be)
     * @var bool
     */
    protected $new = false;

    /**
     * true if exists resource needs to be recreated
     * @var bool
     */
    protected $recreate = false;

    /**
     * Path to base directory (without dynamic path part)
     * @var string
     */
    protected $baseDirectory;
    protected $filePath;

    /**
     * List of object properties that should not be iterable (denied for the usage in response)
     * @var array
     */
    protected $notIterable = [
        'itemPosition',
        'notIterable',
        'baseDirectory',
        'filePath',
        'author',
    ];

    protected $itemPosition = 0;

    public function reset()
    {
        $this->uuid = '';
        $this->name = '';
        $this->nameAlternative = '';
        $this->type = static::TYPE;
        $this->variant = self::DEFAULT_VARIANT;
        $this->version = self::DEFAULT_VERSION;
        $this->author = '';
        $this->baseDirectory = '';
        $this->filePath = '';
        $this->recreate = false;

        return $this;
    }
    abstract public function getMimeType();

    public function __construct()
    {
        $this->reset();
    }
    protected function setUuid()
    {
        $this->uuid = md5($this->name);
    }

    protected function setFilePath()
    {
        $this->filePath = $this->generateFilePath();
    }

    /**
     * /type/variant/version/[other-type-specified/]uuid.type
     * /mp3/default/1/22af64.mp3
     * /mp3/ivona/0/22af64.mp3
     */
    public function generateFilePath()
    {
        return $this->getBaseDirectory()
        . $this->getType() . DIRECTORY_SEPARATOR
        . $this->getVariant() . DIRECTORY_SEPARATOR
        . $this->getVersion() . DIRECTORY_SEPARATOR
        . $this->getUuid() . '.' . $this->getType();
    }

    /**
     * @return mixed
     */
    public function getUuid()
    {
        if (!$this->uuid) {
            $this->setUuid();
        }
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
     * @return ResourceFileDO
     */
    public function setName($name)
    {
        $this->name = (string)$name;
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
     * @return ResourceFileDO
     */
    public function setNameAlternative($nameAlternative)
    {
        $this->nameAlternative = (string)$nameAlternative;
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
     * @return ResourceFileDO
     */
    public function setType($type)
    {
        $this->type = (string)$type;
        $this->setFilePath();

        return $this;
    }

    /**
     * @return string
     */
    public function getVariant()
    {
        if (empty($this->variant)) {
            $this->setVariant();
        }

        return $this->variant;
    }

    /**
     * @param string $variant
     * @return ResourceFileDO
     */
    public function setVariant($variant = self::DEFAULT_VARIANT)
    {
        $this->variant = (string)$variant;
        $this->setFilePath();

        return $this;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        if (self::DEFAULT_VERSION !== $this->version && empty($this->version)) {
            $this->setVersion();
        }
        return $this->version;
    }

    /**
     * @param int $version
     * @return ResourceFileDO
     */
    public function setVersion($version = self::DEFAULT_VERSION)
    {
        $this->version = (int)$version;
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
     * @return ResourceFileDO
     */
    public function setAuthor($author)
    {
        $this->author = (string)$author;

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
    public function getBaseDirectory()
    {
        return $this->baseDirectory;
    }

    /**
     * @param string $dir
     * @return ResourceFileDO
     */
    public function setBaseDirectory($dir)
    {
        $this->baseDirectory = (string)$dir;
        $this->setFilePath();

        return $this;
    }

    /**
     * @return boolean
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * @param boolean $new
     * @return ResourceDOAbstract
     */
    public function setNew($new = false)
    {
        $this->new = $new;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isRecreate()
    {
        return $this->recreate;
    }

    /**
     * @param boolean $recreate
     * @return ResourceDOAbstract
     */
    public function setRecreate($recreate = false)
    {
        $this->recreate = (bool)$recreate;

        return $this;
    }

    public function rewind()
    {
        $this->itemPosition = 0;
    }

    public function current()
    {
        $props = get_object_vars($this);
        $propsNames = array_keys($props);
        sort($propsNames);
        $propName = $propsNames[$this->itemPosition];

        if (!in_array($propName, $this->notIterable)) {

            return [$propName, $props[$propName]];
        }

        return [0, null];
    }

    public function key()
    {
        return $this->itemPosition;
    }

    public function next()
    {
        ++$this->itemPosition;
    }

    public function valid()
    {
        $props = get_object_vars($this);
        $propsNames = array_keys($props);
        sort($propsNames);

        return isset($propsNames[$this->itemPosition]);
    }
    
    public function toArray()
    {
        $ar = [];
        foreach ($this as $k => $p) {
            
            $ar[$p[0]] = $p[1];
        }
        unset($ar[0]);

        return $ar;
    }
}