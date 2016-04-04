<?php
namespace Staticus\Action\Voice;

use AudioManager\Manager;
use Common\Config\Config;
use Staticus\Action\StaticActionAbstract;
use Staticus\Exceptions\ErrorException;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class VoiceActionAbstract extends StaticActionAbstract
{
    protected static $defaultHeaders = [
        'Content-Type' => 'audio/mpeg',
    ];
    public function __construct(Manager $manager, Config $config)
    {
        $this->generator = $manager;
        $this->providerName = $this->getRealClassName($this->generator->getAdapter());
        $this->config = $config->get('voice');
    }

    /**
     * @param $text
     * @param $filePath
     * @return mixed
     */
    protected function generate($text, $filePath)
    {
        $content = $this->generator->read($text);
        $headers = $this->generator->getHeaders();
        if (!isset($headers['http_code']) || $headers['http_code'] != 200) {
            throw new ErrorException(
                'Wrong http response code from voice provider ' . $this->providerName
                . ': ' . $headers['http_code'] . '; Requested text: ' . $text);
        }

        return $content;
    }
}
