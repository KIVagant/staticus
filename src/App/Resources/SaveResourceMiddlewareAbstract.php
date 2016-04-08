<?php
namespace App\Resources;

use App\Resources\File\ResourceFileDO;
use Common\Middleware\MiddlewareAbstract;
use App\Diactoros\FileContentResponse\FileContentResponse;
use App\Resources\Exceptions\SaveResourceErrorException;
use App\Resources\Exceptions\WrongResponseException;
use Zend\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SaveResourceMiddlewareAbstract extends MiddlewareAbstract
{
    protected static $mimeType = 'application/octet-stream';

    private $resourceDO;

    /**
     * Another type for nice IDE autocomplete in child classes
     * @var FileContentResponse
     */
    protected $response;

    public function __construct(ResourceDOInterface $resourceDO)
    {
        $this->resourceDO = $resourceDO;
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
        parent::__invoke($request, $response, $next);
        if (!$response instanceof FileContentResponse) {
            return $next($request, $response);
        }
        $resourceDO = $this->resourceDO;
        $filePath = $resourceDO->getFilePath();
        if (empty($filePath)) {
            throw new WrongResponseException('Empty file path. File can\'t be saved.');
        }
        $this->setHeaders();
        $resourceStream = $response->getResource();
        if (is_resource($resourceStream)) {
            $this->createDirectory(dirname($filePath));
            $this->writeFile($filePath, $resourceStream);
        } else {
            $body = $response->getBody();
            $contents = $body->getContents();
            $this->createDirectory(dirname($filePath));
            $this->writeFile($filePath, $contents);
        }
        $this->copyFileToDefaults($resourceDO);

        return new EmptyResponse($response->getStatusCode(), $response->getHeaders());
    }

    protected function writeFile($filePath, $content)
    {
        if (!file_put_contents($filePath, $content)) {
            throw new SaveResourceErrorException('File cannot be written to the path ' . $filePath);
        }
    }

    /**
     * @param $fromFullPath
     * @param $toFullPath
     * @return bool
     * @throws SaveResourceErrorException
     */
    protected function copyFileIfNotExist($fromFullPath, $toFullPath)
    {
        if (!is_file($toFullPath)) {
            return $this->copyFile($fromFullPath, $toFullPath);
        }

        return true;
    }

    /**
     * @param $fromFullPath
     * @param $toFullPath
     * @return bool
     * @throws SaveResourceErrorException
     */
    protected function copyFile($fromFullPath, $toFullPath)
    {
        $this->createDirectory(dirname($toFullPath));
        if (!copy($fromFullPath, $toFullPath)) {
            throw new SaveResourceErrorException('File cannot be copied to the default path ' . $toFullPath);
        }

        return true;
    }

    protected function setHeaders()
    {
        $fileHeaders = [
            'Content-Type' => static::$mimeType,
        ];
        $headers = $this->response->getHeaders();
        $headers = array_merge($headers, $fileHeaders);
        $this->response->setHeaders($headers);
    }

    /**
     * @param $directory
     */
    protected function createDirectory($directory)
    {
        if (@!mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new SaveResourceErrorException('Can\'t create a directory: ' . $directory);
        }
    }

    protected function copyFileToDefaults(ResourceDOInterface $resourceDO)
    {
        if (ResourceFileDO::DEFAULT_VARIANT !== $resourceDO->getVariant()) {
            $defaultDO = clone $resourceDO;
            $defaultDO->setVariant();
            $defaultDO->setVersion();
            $this->copyFileIfNotExist($resourceDO->getFilePath(), $defaultDO->getFilePath());
        }
        if (ResourceFileDO::DEFAULT_VERSION !== $resourceDO->getVersion()) {
            $defaultDO = clone $resourceDO;
            $defaultDO->setVersion();
            $this->copyFileIfNotExist($resourceDO->getFilePath(), $defaultDO->getFilePath());
        }
    }
}
