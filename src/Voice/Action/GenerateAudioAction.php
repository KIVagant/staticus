<?php

namespace Voice\Action;
use AudioManager\Manager;
use Common\Config\Config;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GenerateAudioAction
{
    protected $audioProviderName;
    protected $audioManager;
    protected $config;
    public function __construct(Manager $audioManager, Config $config)
    {
        $this->audioManager = $audioManager;
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
        try {
            $this->audioProviderName = $this->getRealClassName($this->audioManager->getAdapter());
            $cacheDir = $this->config['cache_dir'] . $this->audioProviderName . '/';
            $text = $request->getAttribute('text');
            $text = rawurldecode($text);
            if (empty($text) || !preg_match('/\w+/u', $text)) {
                $this->throwError('Wrong audio request');
            }
            $extension = $this->config['file_extension'];
            $textHash = md5($text);
            $voiceFilePath = $cacheDir . $textHash . '.' . $extension;
            if (!file_exists($voiceFilePath)) {
                // TODO: Перенести в Middleware для всех проксиков
                $authToken = $request->getHeaderLine('authorization');
                if ($authToken && 'Basic ' . env('AUTH_TOKEN') === $authToken) {
                    $this->generate($text, $voiceFilePath);
                } else {
                    $this->throwError('Access denied');
                }
            }
            $mime = mime_content_type($voiceFilePath);

            return new EmptyResponse(200, [
                'X-Accel-Redirect' => '/' . $voiceFilePath,
                'Content-Type' => $mime,
                // 'Content-Disposition' => 'attachment; filename=' . basename($file)
            ]);
        } catch (\Exception $e) {
            return new EmptyResponse(404);
        }
    }
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
            $this->throwError('Wrong http code from voice audioProvider ' . $this->audioProviderName
                . ': ' . $headers['http_code']);
        }
        file_put_contents($voiceFilePath, $content);
        chmod($voiceFilePath, '0766');
    }

    protected function throwError($error)
    {
        throw new \RuntimeException($error);
    }
}
