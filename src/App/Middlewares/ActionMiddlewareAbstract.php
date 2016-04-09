<?php
namespace App\Middlewares;

use App\Resources\Commands\DeleteSafetyResourceCommand;
use App\Resources\Commands\DestroyResourceCommand;
use App\Resources\PrepareResourceMiddlewareAbstract;
use App\Resources\ResourceDOInterface;
use App\Diactoros\FileContentResponse\FileContentResponse;
use App\Exceptions\ErrorException;
use Staticus\Exceptions\WrongRequestException;
use Zend\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Resources\File\ResourceFileDO;

abstract class ActionMiddlewareAbstract extends MiddlewareAbstract
{
    protected static $defaultHeaders = [];
    /**
     * @var mixed
     */
    protected $generator;
    /**
     * @var ResourceFileDO
     */
    protected $resourceDO;
    /**
     * @var string
     */
    protected $providerName;

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

            return $this->action();
        } catch (WrongRequestException $e) {

            /** @see \Zend\Diactoros\Response::$phrases */
            return new EmptyResponse(400, static::$defaultHeaders);
        }
    }

    abstract protected function action();
    abstract protected function generate(ResourceDOInterface $resourceDO, $filePath);

    protected function getRealClassName($obj)
    {
        $classname = get_class($obj);
        if (preg_match('@\\\\([\w]+)$@', $classname, $matches)) {
            $classname = $matches[1];
        }

        return $classname;
    }

    /**
     * @param $path
     * @param string $filename Filename for saving dialog on the client-side
     * @param bool $forceSaveDialog
     * @return EmptyResponse
     * @throws ErrorException
     */
    protected function XAccelRedirect($path, $filename = '', $forceSaveDialog = false)
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
            if (!$filename) {
                $filename = basename($path);
            }
            $headers['Content-Disposition'] = 'attachment; filename=' . $filename;
        }

        return new EmptyResponse(200, $headers);
    }


    protected function getAction()
    {
        $filePath = realpath($this->resourceDO->getFilePath());
        $filename = $this->resourceDO->getName() . '.' . $this->resourceDO->getType();
        if (file_exists($filePath)) {

            return $this->XAccelRedirect($filePath, $filename, false);
        }

        /** @see \Zend\Diactoros\Response::$phrases */
        return new EmptyResponse(404, static::$defaultHeaders);
    }
    protected function postAction()
    {
        $filePath = $this->resourceDO->getFilePath();
        $fileExists = file_exists($filePath);
        $recreate = PrepareResourceMiddlewareAbstract::getParamFromRequest('recreate', $this->request);
        $recreate = $fileExists && $recreate;
        if (!$fileExists || $recreate) {
            $this->resourceDO->setRecreate($recreate);
            $body = $this->generate($this->resourceDO, $filePath);

            /** @see \Zend\Diactoros\Response::$phrases */
            return new FileContentResponse($body, 201, static::$defaultHeaders);
        }

        /** @see \Zend\Diactoros\Response::$phrases */
        return new EmptyResponse(304, static::$defaultHeaders);
    }
    protected function deleteAction()
    {
        $destroy = PrepareResourceMiddlewareAbstract::getParamFromRequest('destroy', $this->request);
        if ($destroy) {
            $command = new DestroyResourceCommand($this->resourceDO);
            $command->run();
        } else {
            $command = new DeleteSafetyResourceCommand($this->resourceDO);
            $command->run();
        }

        /** @see \Zend\Diactoros\Response::$phrases */
        return new EmptyResponse(204, static::$defaultHeaders);
    }
}