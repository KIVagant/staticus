<?php
namespace Staticus\Resources\Middlewares;

use Staticus\Diactoros\FileContentResponse\FileUploadedResponse;
use Staticus\Resources\Commands\BackupResourceCommand;
use Staticus\Resources\Commands\CopyResourceCommand;
use Staticus\Resources\Commands\DestroyEqualResourceCommand;
use Staticus\Resources\File\ResourceDO;
use Staticus\Middlewares\MiddlewareAbstract;
use Staticus\Diactoros\FileContentResponse\FileContentResponse;
use Staticus\Resources\Exceptions\SaveResourceErrorException;
use Staticus\Diactoros\Exceptions\WrongResponseException;
use Staticus\Resources\ResourceDOInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Stream;
use Zend\Diactoros\UploadedFile;

class SaveResourceMiddlewareAbstract extends MiddlewareAbstract
{
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
        if (
            $response instanceof FileContentResponse
            || $response instanceof FileUploadedResponse
        ) {
            $resourceDO = $this->resourceDO;
            $filePath = $resourceDO->getFilePath();
            if (empty($filePath)) {
                throw new WrongResponseException('Empty file path. File can\'t be saved.');
            }
            $resourceStream = $response->getResource();
            if (is_resource($resourceStream)) {
                $this->save($resourceDO, $resourceStream);
            } else {
                $body = $response->getContent();
                if (!$body) {
                    throw new WrongResponseException('Empty body for generated file. Request: ' . $resourceDO->getName());
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

    protected function writeFile($filePath, $content)
    {
        if (!file_put_contents($filePath, $content)) {
            throw new SaveResourceErrorException('File cannot be written to the path ' . $filePath);
        }
    }

    protected function uploadFile($mime, $content, $filePath)
    {
        $uri = $content->getStream()->getMetadata('uri');
        if (!$uri) {
            throw new SaveResourceErrorException('Unknown error: can\'t get uploaded file uri');
        }
        $uploadedMime = mime_content_type($uri);
        if ($mime !== $uploadedMime) {
            throw new SaveResourceErrorException('Bad request: incorrect mime-type of the uploaded file');
        }
        $content->moveTo($filePath);
    }

    protected function copyResource(ResourceDOInterface $originResourceDO, ResourceDOInterface $newResourceDO)
    {
        $command = new CopyResourceCommand($originResourceDO, $newResourceDO);

        return $command();
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

    /**
     * @param $directory
     * @deprecated
     */
    protected function createDirectory($directory)
    {
        if (@!mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new SaveResourceErrorException('Can\'t create a directory: ' . $directory);
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
            $command = new BackupResourceCommand($resourceDO);
            $backupResourceVerDO = $command();
        }
        if ($content instanceof UploadedFile) {
            $this->uploadFile($resourceDO->getMimeType(), $content, $filePath);
        } else {
            $this->writeFile($filePath, $content);
        }

        if ($backupResourceVerDO instanceof ResourceDOInterface
            && $backupResourceVerDO->getVersion() !== ResourceDOInterface::DEFAULT_VERSION) {

            // If the newly created file is the same as the previous version, remove it immediately
            $command = new DestroyEqualResourceCommand($resourceDO, $backupResourceVerDO);
            $command();
        }

        return $resourceDO;
    }
}
