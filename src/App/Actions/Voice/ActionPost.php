<?php
namespace App\Actions\Voice;

use Staticus\Exceptions\ErrorException;
use Staticus\Middlewares\ActionPostAbstract;
use Staticus\Resources\Mpeg\ResourceDO;
use Staticus\Resources\ResourceDOInterface;
use AudioManager\Manager;

class ActionPost extends ActionPostAbstract
{
    public function __construct(ResourceDO $resourceDO, Manager $manager)
    {
        $this->resourceDO = $resourceDO;
        $this->generator = $manager;
    }

    /**
     * @param ResourceDOInterface $resourceDO
     * @param $filePath
     * @return mixed
     * @throws ErrorException
     */
    protected function generate(ResourceDOInterface $resourceDO, $filePath)
    {
        $alternative = $resourceDO->getNameAlternative();
        $voiceText = $alternative
            ? $alternative
            : $resourceDO->getName();
        $content = $this->generator->read($voiceText);
        $headers = $this->generator->getHeaders();
        if (!isset($headers['http_code']) || $headers['http_code'] != 200) {
            throw new ErrorException(
                'Wrong http response code from voice provider ' . get_class($this->generator->getAdapter())
                . ': ' . $headers['http_code'] . '; Requested text: ' . $resourceDO->getName());
        }

        return $content;
    }
}