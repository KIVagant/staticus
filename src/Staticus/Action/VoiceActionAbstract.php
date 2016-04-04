<?php
namespace Staticus\Action;

use AudioManager\Manager;
use Common\Config\Config;
use Staticus\Exceptions\ErrorException;
use Staticus\Exceptions\WrongRequestException;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class VoiceActionAbstract extends StaticActionAbstract
{
    /**
     * @var Manager
     */
    protected $audioManager;

    public function __construct(Manager $audioManager, Config $config)
    {
        $this->audioManager = $audioManager;
        $this->providerName = $this->getRealClassName($this->audioManager->getAdapter());
        $this->config = $config->get('voice');
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable|null $next
     * @return EmptyResponse
     * @throws \Exception
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    )
    {
        $this->request = $request;
        $this->response = $response;
        $this->next = $next;
        try {
            $cacheDir = $this->config['cache_dir'] . $this->providerName . '/';
            $this->prepareParamText($request);

            $extension = $this->config['file_extension'];
            $this->filePath = $cacheDir . $this->textHash . '.' . $extension;

            return $this->action();
        } catch (WrongRequestException $e) {

            return new EmptyResponse(400, static::$defaultHeaders);
        }
    }

    /**
     * @param $text
     * @param $voiceFilePath
     */
    protected function generate($text, $voiceFilePath)
    {
        $content = $this->audioManager->read($text);
        $headers = $this->audioManager->getHeaders();
        if (!isset($headers['http_code']) || $headers['http_code'] != 200) {
            throw new ErrorException(
                'Wrong http response code from voice provider ' . $this->providerName
                . ': ' . $headers['http_code'] . '; Requested text: ' . $text);
        }
        if (!file_put_contents($voiceFilePath, $content)) {
            throw new ErrorException('File cannot be written to path ' . $voiceFilePath);
        }
        if (!chmod($voiceFilePath, '0766')) {
            throw new ErrorException('Cannot setup file permissions for ' . $voiceFilePath);
        }
    }
}
