<?php
namespace Staticus\Action;

use Staticus\Exceptions\ErrorException;
use Staticus\Exceptions\WrongRequestException;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class StaticActionAbstract
{
    protected static $defaultHeaders = [];
    /**
     * @var mixed
     */
    protected $generator;
    /**
     * @var string
     */
    protected $providerName;
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
    protected $filePath;
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
            $cacheDir = $this->config['cache_dir'] . strtolower($this->providerName) . '/';
            $this->prepareParamText($request);

            $extension = $this->config['file_extension'];
            $this->filePath = $cacheDir . $this->textHash . '.' . $extension;

            return $this->action();
        } catch (WrongRequestException $e) {

            return new EmptyResponse(400, static::$defaultHeaders);
        }
    }

    abstract protected function action();
    abstract protected function generate($text, $filePath);

    protected function getRealClassName($obj)
    {
        $classname = get_class($obj);
        if (preg_match('@\\\\([\w]+)$@', $classname, $matches)) {
            $classname = $matches[1];
        }

        return $classname;
    }

    /**
     * @return EmptyResponse
     */
    protected function XAccelRedirect($path, $forceSaveDialog = false)
    {
        $mime = mime_content_type($path);
        $headers = [
            'X-Accel-Redirect' => '/' . $path,
            'Content-Type' => $mime,
            // '' =>
        ];
        if ($forceSaveDialog) {
            $headers['Content-Disposition'] = 'attachment; filename=' . basename($this->text);
        }

        return new EmptyResponse(200, $headers);
    }

    /**
     * @param ServerRequestInterface $request
     */
    protected function prepareParamText(ServerRequestInterface $request)
    {
        $text = $request->getAttribute('text');
        $text = mb_strtolower(rawurldecode($text), 'UTF-8');
        if (empty($text) || !preg_match('/\w+/u', $text)) {
            throw new WrongRequestException('Wrong audio request');
        }
        $this->text = $text;
        $this->textHash = md5($text);
    }
    /**
     * @param $filePath
     * @param $image
     */
    protected function writeFile($filePath, $image)
    {
        if (!file_put_contents($filePath, $image)) {
            throw new ErrorException('File cannot be written to path ' . $filePath);
        }
        if (!chmod($filePath, '0766')) {
            throw new ErrorException('Cannot setup file permissions for ' . $filePath);
        }
    }
    protected function getAction()
    {
        if (file_exists($this->filePath)) {

            return $this->XAccelRedirect($this->filePath);
        }

        return new EmptyResponse(404, static::$defaultHeaders);
    }
    protected function postAction()
    {
        $params = $this->request->getQueryParams('recreate');
        if (!file_exists($this->filePath) || !empty($params['recreate'])) {
            $content = $this->generate($this->text, $this->filePath);
            $this->writeFile($this->filePath, $content);

            // HTTP 201 Created
            return new EmptyResponse(201, static::$defaultHeaders);
        }

        // HTTP 304 Not Modified
        return new EmptyResponse(304, static::$defaultHeaders);
    }
    protected function deleteAction()
    {
        // HTTP 204 No content
        if (file_exists($this->filePath)) {
            if (unlink($this->filePath)) {

                return new EmptyResponse(204, static::$defaultHeaders);
            } else {
                throw new ErrorException('The file cannot be removed: ' . $this->filePath);
            }
        }

        return new EmptyResponse(204, static::$defaultHeaders);
    }
}
