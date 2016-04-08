<?php
namespace Staticus\Action;

use App\Action\ActionAbstract;
use App\Diactoros\FileContentResponse\FileContentResponse;
use Staticus\Exceptions\ErrorException;
use Staticus\Exceptions\WrongRequestException;
use Zend\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class StaticActionAbstract extends ActionAbstract
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
        parent::__invoke($request, $response, $next);
        try {
            $this->prepareParamText($request); // TODO: вынести в слой кеша в MiddleWare

            $cacheDir = $this->config['cache_dir'] . strtolower($this->providerName) . '/';
            $extension = $this->config['file_extension'];
            $this->filePath = $cacheDir . $this->textHash . '.' . $extension;

            return $this->action();
        } catch (WrongRequestException $e) {

            /** @see \Zend\Diactoros\Response::$phrases */
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
        if (!$mime) {
            throw new ErrorException('Mime content type can not be reached');
        }
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
        $text = trim(mb_strtolower(rawurldecode($text)), 'UTF-8');
        if (empty($text) || !preg_match('/\w+/u', $text)) {
            throw new WrongRequestException('Wrong audio request');
        }
        $this->text = $text;
        $this->textHash = md5($text);
    }

    protected function getAction()
    {
        if (file_exists($this->filePath)) {

            return $this->XAccelRedirect($this->filePath);
        }

        /** @see \Zend\Diactoros\Response::$phrases */
        return new EmptyResponse(404, static::$defaultHeaders);
    }
    protected function postAction()
    {
        $params = $this->request->getQueryParams('recreate');
        if (!file_exists($this->filePath) || !empty($params['recreate'])) {
            $body = $this->generate($this->text, $this->filePath);

            /** @see \Zend\Diactoros\Response::$phrases */
            return new FileContentResponse($this->filePath, $body, 201, static::$defaultHeaders);
        }

        /** @see \Zend\Diactoros\Response::$phrases */
        return new EmptyResponse(304, static::$defaultHeaders);
    }
    protected function deleteAction()
    {
        if (file_exists($this->filePath)) {
            if (unlink($this->filePath)) {

                /** @see \Zend\Diactoros\Response::$phrases */
                return new EmptyResponse(204, static::$defaultHeaders);
            } else {
                throw new ErrorException('The file cannot be removed: ' . $this->filePath);
            }
        }

        /** @see \Zend\Diactoros\Response::$phrases */
        return new EmptyResponse(204, static::$defaultHeaders);
    }
}