<?php
namespace Staticus\Resources\Middlewares;

use Staticus\Diactoros\FileContentResponse\ResourceDoResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Staticus\Resources\CropDO;
use Staticus\Resources\Exceptions\SaveResourceErrorException;
use Staticus\Resources\Image\ImagePostProcessingAbstract;

abstract class ImageCropMiddlewareAbstract extends ImagePostProcessingAbstract
{
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
                $targetResourceDO = $this->chooseTargetResource($response);

                $defaultImagePath = $targetResourceDO->getFilePath();
                if (is_file($defaultImagePath)) {
                    $this->cropImage($defaultImagePath, $resourceDO->getFilePath(), $crop);
                }
            }
        }
        $response = new ResourceDoResponse($resourceDO, $response->getStatusCode(), $response->getHeaders());

        return $next($request, $response);
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
        $imagick->writeImage($destinationPath);
    }
}
