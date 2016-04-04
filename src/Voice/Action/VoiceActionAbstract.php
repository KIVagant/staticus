<?php
namespace Voice\Action;

use AudioManager\Manager;
use Common\Config\Config;
use Voice\Exceptions\VoiceErrorException;
use Voice\Exceptions\VoiceWrongRequestException;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class VoiceActionAbstract
{
    protected static $defaultHeaders = [
        'Content-Type' => 'audio/mpeg',
    ];
    /**
     * @var string
     */
    protected $audioProviderName;
    /**
     * @var Manager
     */
    protected $audioManager;
    /**
     * @var array
     */
    protected $config;
    /**
     * @var string
     */
    protected $text;
    /**
     * @var string
     */
    protected $textHash;
    /**
     * @var string
     */
    protected $voiceFilePath;
    /**
     * @var ServerRequestInterface
     */
    protected $request;
    /**
     * @var ResponseInterface
     */
    protected $response;
    /**
     * @var callable
     */
    protected $next;


    public function __construct(Manager $audioManager, Config $config)
    {
        $this->audioManager = $audioManager;
        $this->audioProviderName = $this->getRealClassName($this->audioManager->getAdapter());
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
            $cacheDir = $this->config['cache_dir'] . $this->audioProviderName . '/';
            $this->prepareParamText($request);

            $extension = $this->config['file_extension'];
            $this->voiceFilePath = $cacheDir . $this->textHash . '.' . $extension;

            return $this->action();
        } catch (VoiceWrongRequestException $e) {

            return new EmptyResponse(400, static::$defaultHeaders);
        }
    }
    abstract protected function action();

    protected function getRealClassName($obj)
    {
        $classname = get_class($obj);
        if (preg_match('@\\\\([\w]+)$@', $classname, $matches)) {
            $classname = $matches[1];
        }

        return $classname;
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
            throw new VoiceErrorException(
                'Wrong http response code from voice provider ' . $this->audioProviderName
                . ': ' . $headers['http_code'] . '; Requested text: ' . $text);
        }
        if (!file_put_contents($voiceFilePath, $content)) {
            throw new VoiceErrorException('File cannot be written to path ' . $voiceFilePath);
        }
        if (!chmod($voiceFilePath, '0766')) {
            throw new VoiceErrorException('Cannot setup file permissions for ' . $voiceFilePath);
        }
    }

    /**
     * @return EmptyResponse
     */
    protected function XAccelRedirect($path)
    {
        $mime = mime_content_type($path);

        return new EmptyResponse(200, [
            'X-Accel-Redirect' => '/' . $path,
            'Content-Type' => $mime,
            // 'Content-Disposition' => 'attachment; filename=' . basename($file)
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     */
    protected function prepareParamText(ServerRequestInterface $request)
    {
        $text = $request->getAttribute('text');
        $text = mb_strtolower(rawurldecode($text), 'UTF-8');
        if (empty($text) || !preg_match('/\w+/u', $text)) {
            throw new VoiceWrongRequestException('Wrong audio request');
        }
        $this->text = $text;
        $this->textHash = md5($text);
    }
}
