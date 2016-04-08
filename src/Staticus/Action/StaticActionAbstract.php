<?php
namespace Staticus\Action;

use Common\Middleware\MiddlewareAbstract;
use App\Diactoros\FileContentResponse\FileContentResponse;
use Staticus\Exceptions\ErrorException;
use Staticus\Exceptions\WrongRequestException;
use Zend\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Staticus\Resource\ResourceDO;

abstract class StaticMiddlewareAbstract extends MiddlewareAbstract
{
    protected static $defaultHeaders = [];
    /**
     * @var mixed
     */
    protected $generator;
    /**
     * @var ResourceDO
     */
    protected $resourceDO;
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
            $cacheDir = $this->config['cache_dir'] . strtolower($this->providerName) . '/';
            $this->resourceDO->setDirectory($cacheDir);

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


    protected function getAction()
    {
        $path = $this->resourceDO->getFilePath();
        if (file_exists($path)) {

            return $this->XAccelRedirect($path);
        }

        /** @see \Zend\Diactoros\Response::$phrases */
        return new EmptyResponse(404, static::$defaultHeaders);
    }
    protected function postAction()
    {
        $params = $this->request->getQueryParams('recreate');
        $path = $this->resourceDO->getFilePath();
        if (!file_exists($path) || !empty($params['recreate'])) {
            $body = $this->generate($this->text, $path);

            /** @see \Zend\Diactoros\Response::$phrases */
            return new FileContentResponse($body, 201, static::$defaultHeaders);
        }

        /** @see \Zend\Diactoros\Response::$phrases */
        return new EmptyResponse(304, static::$defaultHeaders);
    }
    protected function deleteAction()
    {
        $path = $this->resourceDO->getFilePath();
        if (file_exists($path)) {
            if (unlink($path)) {

                /** @see \Zend\Diactoros\Response::$phrases */
                return new EmptyResponse(204, static::$defaultHeaders);
            } else {
                throw new ErrorException('The file cannot be removed: ' . $path);
            }
        }

        /** @see \Zend\Diactoros\Response::$phrases */
        return new EmptyResponse(204, static::$defaultHeaders);
    }
}