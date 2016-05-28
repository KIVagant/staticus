<?php
namespace Staticus\Resources\Middlewares\Image;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Staticus\Diactoros\FileContentResponse\ResourceDoResponse;

abstract class ImageResizeMiddlewareAbstract extends ImagePostProcessingMiddlewareAbstract
{

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    )
    {
        parent::__invoke($request, $response, $next);
        if (!$this->isSupportedResponse($response)) {

            return $next($request, $response);
        }
        $resourceDO = $this->resourceDO;
        if ($resourceDO->getSize()) {
            if ($resourceDO->isNew() // For POST method
                || $resourceDO->isRecreate() // For POST method
                || !$this->filesystem->has($resourceDO->getFilePath()) // For GET method
            ) {
                $targetResourceDO = $this->chooseTargetResource($response);

                $defaultImagePath = $targetResourceDO->getFilePath();
                if ($this->filesystem->has($defaultImagePath)) {
                    $this->resizeImage($defaultImagePath, $resourceDO->getFilePath(), $resourceDO->getWidth(), $resourceDO->getHeight());
                }
            }
        }

        $response = new ResourceDoResponse($resourceDO, $response->getStatusCode(), $response->getHeaders());

        return $next($request, $response);
    }

    public function resizeImage($sourcePath, $destinationPath, $width, $height)
    {
        $this->createDirectory(dirname($destinationPath));
        $imagick = $this->getImagick($sourcePath);
        $imagick->adaptiveResizeImage($width, $height, true);
        $imagick->writeImage($destinationPath);
        $imagick->clear();
        $imagick->destroy();
    }
}
