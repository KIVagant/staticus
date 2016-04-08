<?php
namespace Staticus\Action\Voice;

use App\Resources\ResourceDOInterface;
use AudioManager\Manager;
use Common\Config\Config;
use Staticus\Action\StaticMiddlewareAbstract;
use Staticus\Exceptions\ErrorException;
use App\Resources\File\ResourceFileDO;

abstract class VoiceActionAbstract extends StaticMiddlewareAbstract
{
    protected static $defaultHeaders = [
        'Content-Type' => 'audio/mpeg',
    ];

    public function __construct(ResourceFileDO $resourceDO, Manager $manager, Config $config)
    {
        $this->resourceDO = $resourceDO;
        $this->generator = $manager;
        $this->providerName = $this->getRealClassName($this->generator->getAdapter());
        $this->config = $config;
    }

    /**
     * @param \App\Resources\ResourceDOInterface $resourceDO
     * @param $filePath
     * @return mixed
     * @internal param $text
     */
    protected function generate(ResourceDOInterface $resourceDO, $filePath)
    {
        $content = $this->generator->read($resourceDO->getName());
        $headers = $this->generator->getHeaders();
        if (!isset($headers['http_code']) || $headers['http_code'] != 200) {
            throw new ErrorException(
                'Wrong http response code from voice provider ' . $this->providerName
                . ': ' . $headers['http_code'] . '; Requested text: ' . $resourceDO->getName());
        }

        return $content;
    }
}
