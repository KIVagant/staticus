<?php
namespace App\Actions\Voice;

use League\Flysystem\FilesystemInterface;
use Staticus\Exceptions\ErrorException;
use Staticus\Middlewares\ActionPostAbstract;
use Staticus\Resources\Mpeg\ResourceDO;
use Staticus\Resources\ResourceDOInterface;
use AudioManager\Manager;

class ActionPost extends ActionPostAbstract
{
    public function __construct(ResourceDO $resourceDO, FilesystemInterface $filesystem, Manager $manager)
    {
        parent::__construct($resourceDO, $filesystem, $manager, null);
    }
    /**
     * @param ResourceDOInterface $resourceDO
     * @return mixed
     * @throws ErrorException
     */
    protected function generate(ResourceDOInterface $resourceDO)
    {
        /** @var Manager $generator */
        $generator = $this->generator;
        $alternative = $resourceDO->getNameAlternative();
        $voiceText = $alternative ?: $resourceDO->getName();
        $content = $generator->read($voiceText);
        $headers = $generator->getHeaders();
        if (!array_key_exists('http_code', (array)$headers) || $headers['http_code'] != 200) {
            throw new ErrorException(
                'Wrong http response code from voice provider '
                . get_class($this->generator->getAdapter())
                . ': ' . $headers['http_code'] . '; Requested text: '
                . $resourceDO->getName(), __LINE__);
        }

        return $content;
    }
}