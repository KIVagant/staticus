<?php
namespace Staticus\Resources\Middlewares;

use Staticus\Middlewares\MiddlewareAbstract;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Staticus\Resources\CropDO;
use Staticus\Resources\Exceptions\SaveResourceErrorException;
use Staticus\Resources\ResourceDOInterface;

abstract class ImageCropMiddlewareAbstract extends MiddlewareAbstract
{
    protected $resourceDO;
    public function __construct(ResourceDOInterface $resourceDO)
    {
        $this->resourceDO = $resourceDO;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    )
    {
        parent::__invoke($request, $response, $next);

        $resourceDO = $this->resourceDO;
        $crop = $resourceDO->getCrop();
        if ($resourceDO->getSize() && $crop) {
            if ($resourceDO->isNew() // For POST method
                || $resourceDO->isRecreate() // For POST method
                || !is_file($resourceDO->getFilePath()) // For GET method
            ) {
                $defaultImagePath = $request->getAttribute('defaultImagePath', $this->getDefaultImagePath());
                if (is_file($defaultImagePath)) {
                    $this->cropImage($defaultImagePath, $resourceDO->getFilePath(), $crop);
                    $request = $request->withAttribute('defaultImagePath', $resourceDO->getFilePath());
                }
            }
        }

        return $next($request, $response);
    }

    protected function getDefaultImagePath()
    {
        $defaultSizeResourceDO = clone $this->resourceDO;
        $defaultSizeResourceDO->setWidth();
        $defaultSizeResourceDO->setHeight();

        return $defaultSizeResourceDO->getFilePath();
    }

    public function cropImage($sourcePath, $destinationPath, CropDO $crop)
    {
        $this->createDirectory(dirname($destinationPath));
        $imagick = new \Imagick(realpath($sourcePath));
        $imagick->cropImage(
            $crop->getWidth(),
            $crop->getHeight(),
            $crop->getX(),
            $crop->getY()
        );
        $resource = fopen($destinationPath, "w");
        if (!$resource) {
            throw new SaveResourceErrorException('Can\'t open file for write: ' . $destinationPath, __LINE__);
        }
        $imagick->writeImageFile($resource);
        fclose($resource);
    }
    /**
     * @param $directory
     * @throws SaveResourceErrorException
     * @deprecated
     * @todo move file operations somewhere
     * @see \Staticus\Resources\Middlewares\SaveResourceMiddlewareAbstract::createDirectory
     */
    protected function createDirectory($directory)
    {
        if (@!mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new SaveResourceErrorException('Can\'t create a directory: ' . $directory, __LINE__);
        }
    }
}
