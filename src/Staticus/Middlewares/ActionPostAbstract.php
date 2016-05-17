<?php
namespace Staticus\Middlewares;

use League\Flysystem\FilesystemInterface;
use Staticus\Diactoros\DownloadedFile;
use Staticus\Diactoros\FileContentResponse\FileUploadedResponse;
use Staticus\Exceptions\ErrorException;
use Staticus\Resources\Middlewares\PrepareResourceMiddlewareAbstract;
use Staticus\Resources\ResourceDOInterface;
use Staticus\Diactoros\FileContentResponse\FileContentResponse;
use Zend\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Staticus\Resources\File\ResourceDO;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\UploadedFile;

abstract class ActionPostAbstract extends MiddlewareAbstract
{
    const RECREATE_COMMAND = 'recreate';
    const SEARCH_COMMAND = 'search';
    const URI_COMMAND = 'uri';
    const CURL_TIMEOUT = 15;
    /**
     * Generator provider
     * @var mixed
     */
    protected $generator;

    /**
     * Search provider
     * @var mixed
     */
    protected $searcher;

    /**
     * @var ResourceDO
     */
    protected $resourceDO;
    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    public function __construct(
        ResourceDOInterface $resourceDO, FilesystemInterface $filesystem, $fractal, $generatorSearch)
    {
        $this->resourceDO = $resourceDO;
        $this->generator = $fractal;
        $this->searcher = $generatorSearch;
        $this->filesystem = $filesystem;
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
        $this->response = $this->action();

        return $this->next();
    }

    abstract protected function generate(ResourceDOInterface $resourceDO);
    abstract protected function search(ResourceDOInterface $resourceDO);

    protected function action()
    {
        $headers = [
            'Content-Type' => $this->resourceDO->getMimeType(),
        ];
        $filePath = $this->resourceDO->getFilePath();
        $fileExists = is_file($filePath);
        $recreate = PrepareResourceMiddlewareAbstract::getParamFromRequest(static::RECREATE_COMMAND, $this->request);
        $search = PrepareResourceMiddlewareAbstract::getParamFromRequest(static::SEARCH_COMMAND, $this->request);
        $uri = PrepareResourceMiddlewareAbstract::getParamFromRequest(static::URI_COMMAND, $this->request);
        $recreate = $fileExists && $recreate;
        $this->resourceDO->setNew(!$fileExists);
        if (!$fileExists || $recreate) {
            $this->resourceDO->setRecreate($recreate);
            $upload = $this->upload();

            // Upload must be with high priority
            if ($upload) {

                /** @see \Zend\Diactoros\Response::$phrases */
                return new FileUploadedResponse($upload, 201, $headers);
            } elseif ($uri) {
                $upload = $this->download($this->resourceDO, $uri);

                /** @see \Zend\Diactoros\Response::$phrases */
                return new FileUploadedResponse($upload, 201, $headers);
            } elseif ($search) {
                $response = $this->search($this->resourceDO);

                return new JsonResponse(['found' => $response]);
            } else {
                $body = $this->generate($this->resourceDO);

                /** @see \Zend\Diactoros\Response::$phrases */
                return new FileContentResponse($body, 201, $headers);
            }

        }

        /** @see \Zend\Diactoros\Response::$phrases */
        return new EmptyResponse(304, $headers);
    }

    /**
     * @return string|null
     */
    protected function upload()
    {
        $uploaded = $this->request->getUploadedFiles();
        $uploaded = current($uploaded);
        if ($uploaded instanceof UploadedFile) {

            return $uploaded;
        }

        return null;
    }

    /**
     * @param ResourceDOInterface $resourceDO
     * @param $uri
     * @return UploadedFile
     * @throws ErrorException
     * @throws \Exception
     */
    protected function download(ResourceDOInterface $resourceDO, $uri)
    {
        // ------------
        // @todo refactoring: move downloading code from here to separate service!
        // ------------
        set_time_limit(self::CURL_TIMEOUT);
        $dir = DATA_DIR . 'download' . DIRECTORY_SEPARATOR;
        $file = $this->resourceDO->getUuid() . '_' . time() . '_' . mt_rand(100, 200) . '.tmp';
        if(!@mkdir($dir) && !is_dir($dir)) {
            throw new ErrorException('Can\'t create the directory: ' . $dir, __LINE__);
        }
        if (is_file($file)) {
            if(!unlink($file)) {
                throw new ErrorException('Can\'t remove old file: ' . $dir . $file, __LINE__);
            }
        }
        $resource = fopen($dir . $file, 'w+');
        if (!$resource) {
            throw new ErrorException('Can\'t create the file for writting: ' . $dir . $file, __LINE__);
        }
        $uriEnc = str_replace(' ', '%20', $uri);
        $headers = [
            "Accept: " . $resourceDO->getMimeType(),
            "Cache-Control: no-cache",
            "Pragma: no-cache",
        ];
        $ch = curl_init($uriEnc);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, static::CURL_TIMEOUT);
        // Save curl result to the file
        curl_setopt($ch, CURLOPT_FILE, $resource);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // get curl response
        curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close($ch);
            fclose($resource);
            throw new ErrorException('Curl error for uri: ' . $uri . '; ' . curl_error($ch), __LINE__);
        }
        $size = (int)curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);
        fclose($resource);
        // ------------

        $downloaded = new DownloadedFile($dir . $file, $size, UPLOAD_ERR_OK, $resourceDO->getName() . '.' . $resourceDO->getType(), $resourceDO->getMimeType());

        return $downloaded;
    }
}