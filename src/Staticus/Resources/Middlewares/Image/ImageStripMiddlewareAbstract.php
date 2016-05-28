<?php
namespace Staticus\Resources\Middlewares\Image;

use League\Flysystem\FilesystemInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Staticus\Config\ConfigInterface;
use Staticus\Resources\ResourceDOInterface;

abstract class ImageStripMiddlewareAbstract extends ImagePostProcessingMiddlewareAbstract
{
    /**
     * @var ConfigInterface
     */
    public $config;

    public function __construct(ResourceDOInterface $resourceDO, FilesystemInterface $filesystem, ConfigInterface $config)
    {
        parent::__construct($resourceDO, $filesystem);
        $this->config = $config;
    }

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
        if ($this->mustBeStripped()) {
            $this->stripImage($this->resourceDO->getFilePath());
        }

        return $next($request, $response);
    }

    public function stripImage($sourcePath)
    {
        $imagick = $this->getImagick($sourcePath);
        $imagick->stripImage();
        $imagick->writeImage($sourcePath);
        $imagick->clear();
        $imagick->destroy();
    }

    /**
     * @return bool
     */
    protected function mustBeStripped()
    {
        return $this->config->get('staticus.images.exif.strip', false)
        && (
            $this->resourceDO->isNew() // For the POST method
            || $this->resourceDO->isRecreate() // For the POST method
        )
        && $this->filesystem->has($this->resourceDO->getFilePath());
    }
}
