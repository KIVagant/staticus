<?php
namespace Staticus\Resources\Middlewares;

use Psr\Http\Message\UploadedFileInterface;
use Staticus\Exceptions\WrongRequestException;
use Staticus\Diactoros\FileContentResponse\FileUploadedResponse;
use Staticus\Resources\Commands\BackupResourceCommand;
use Staticus\Resources\Commands\CopyResourceCommand;
use Staticus\Resources\Commands\DestroyEqualResourceCommand;
use Staticus\Resources\File\ResourceDO;
use Staticus\Middlewares\MiddlewareAbstract;
use Staticus\Diactoros\FileContentResponse\FileContentResponse;
use Staticus\Resources\Exceptions\SaveResourceErrorException;
use Staticus\Exceptions\WrongResponseException;
use Staticus\Resources\ResourceDOInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Stream;

abstract class SaveResourceMiddlewareAbstract extends MiddlewareAbstract
{
    protected $resourceDO;

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
        if (
            $response instanceof FileContentResponse
            || $response instanceof FileUploadedResponse
        ) {
            $resourceDO = $this->resourceDO;
            $filePath = $resourceDO->getFilePath();
            if (empty($filePath)) {
                throw new WrongResponseException('Empty file path. File can\'t be saved.', __LINE__);
            }
            $resourceStream = $response->getResource();
            if (is_resource($resourceStream)) {
                $this->save($resourceDO, $resourceStream);
            } else {
                $body = $response->getContent();
                if (!$body) {
                    throw new WrongResponseException('Empty body for generated file. Request: ' . $resourceDO->getName(), __LINE__);
                }
                $this->save($resourceDO, $body);
            }
            $this->copyFileToDefaults($resourceDO);
            $this->response = new EmptyResponse($response->getStatusCode(), [
                'Content-Type' => $this->resourceDO->getMimeType(),
            ]);
        }

        return $this->next();
    }

    /**
     * @param $filePath
     * @param $content
     * @todo move file operations somethere
     */
    protected function writeFile($filePath, $content)
    {
        if (!file_put_contents($filePath, $content)) {
            throw new SaveResourceErrorException('File cannot be written to the path ' . $filePath, __LINE__);
        }
    }

    protected function uploadFile(UploadedFileInterface $content, $mime, $filePath)
    {
        $uri = $content->getStream()->getMetadata('uri');
        if (!$uri) {
            throw new SaveResourceErrorException('Unknown error: can\'t get uploaded file uri', __LINE__);
        }
        $uploadedMime = mime_content_type($uri);
        if ($mime !== $uploadedMime) {
            /**
             * Try to remove unnecessary file because UploadFile object can be emulated
             * @see \Staticus\Middlewares\ActionPostAbstract::download
             */
            @unlink($uri);
            throw new WrongRequestException('Bad request: incorrect mime-type of the uploaded file', __LINE__);
        }
        $content->moveTo($filePath);
    }

    protected function copyResource(ResourceDOInterface $originResourceDO, ResourceDOInterface $newResourceDO)
    {
        $command = new CopyResourceCommand($originResourceDO, $newResourceDO);

        return $command();
    }

    /**
     * @param $directory
     * @throws SaveResourceErrorException
     * @deprecated
     * @todo move file operations somethere
     * @see \Staticus\Resources\ImagePostProcessingMiddlewareAbstract::createDirectory
     */
    protected function createDirectory($directory)
    {
        if (@!mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new SaveResourceErrorException('Can\'t create a directory: ' . $directory, __LINE__);
        }
    }

    protected function copyFileToDefaults(ResourceDOInterface $resourceDO)
    {
        if (ResourceDO::DEFAULT_VARIANT !== $resourceDO->getVariant()) {
            $defaultDO = clone $resourceDO;
            $defaultDO->setVariant();
            $defaultDO->setVersion();
            $this->copyResource($resourceDO, $defaultDO);
        }
        if (ResourceDO::DEFAULT_VERSION !== $resourceDO->getVersion()) {
            $defaultDO = clone $resourceDO;
            $defaultDO->setVersion();
            $this->copyResource($resourceDO, $defaultDO);
        }
    }

    /**
     * @param ResourceDOInterface $resourceDO
     * @param string|resource|Stream $content
     * @return ResourceDOInterface
     * @throws \RuntimeException if the upload was not successful.
     * @throws \InvalidArgumentException if the $path specified is invalid.
     * @throws \RuntimeException on any error during the move operation, or on
     */
    protected function save(ResourceDOInterface $resourceDO, $content)
    {
        $backupResourceVerDO = null;
        $filePath = $resourceDO->getFilePath();
        $this->createDirectory(dirname($filePath));
        // backups don't needs if this is a 'new creation' command
        if ($resourceDO->isRecreate()) {
            $backupResourceVerDO = $this->backup($resourceDO);
        }
        if ($content instanceof UploadedFileInterface) {
            $this->uploadFile($content, $resourceDO->getMimeType(), $filePath);
        } else {
            $this->writeFile($filePath, $content);
        }

        $responseDO = $resourceDO;
        if ($backupResourceVerDO instanceof ResourceDOInterface
            && $backupResourceVerDO->getVersion() !== ResourceDOInterface::DEFAULT_VERSION) {
            // If the newly created file is the same as the previous version, remove backup immediately
            $responseDO = $this->destroyEqual($resourceDO, $backupResourceVerDO);
        }
        if ($responseDO === $resourceDO) {

            // cleanup postprocessing cache folders
            // - if it is a new file creation (remove possible garbage after other operations)
            // - or if the basic file is replaced and not equal to the previous version
            $this->afterSave($resourceDO);
        }

        return $resourceDO;
    }
    abstract protected function afterSave(ResourceDOInterface $resourceDO);

    protected function backup(ResourceDOInterface $resourceDO)
    {
        $command = new BackupResourceCommand($resourceDO);
        $backupResourceVerDO = $command();

        return $backupResourceVerDO;
    }

    /**
     * @param ResourceDOInterface $resourceDO
     * @param ResourceDOInterface $backupResourceVerDO
     * @return mixed
     */
    protected function destroyEqual(ResourceDOInterface $resourceDO, ResourceDOInterface $backupResourceVerDO)
    {
        $command = new DestroyEqualResourceCommand($resourceDO, $backupResourceVerDO);
        $responseDO = $command();

        return $responseDO;
    }
}
