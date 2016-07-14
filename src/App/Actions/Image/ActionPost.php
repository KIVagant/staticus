<?php
namespace App\Actions\Image;

use League\Flysystem\FilesystemInterface;
use Staticus\Middlewares\ActionPostAbstract;
use Staticus\Resources\ResourceDOInterface;
use Staticus\Resources\Image\ResourceImageDOInterface;
use FractalManager\Manager as FractalManager;

class ActionPost extends ActionPostAbstract
{
    public function __construct(
        ResourceImageDOInterface $resourceDO
        , FilesystemInterface $filesystem
        , FractalManager $fractal
    )
    {
        parent::__construct($resourceDO, $filesystem, $fractal);
    }

    /**
     * @param ResourceDOInterface|ResourceImageDOInterface $resourceDO
     * @return mixed
     */
    protected function generate(ResourceDOInterface $resourceDO)
    {
        // Do not generate image when resizing or cropping is requested
        if ($this->resourceDO->getDimension()) {

            // If recreation for current size is asked, just remove previous file
            // Without this next middlewares will try to resize exist file
            if ($this->resourceDO->isRecreate()) {
                $this->filesystem->delete($this->resourceDO->getFilePath());
            }

            return null;
        }
        $query = $resourceDO->getName() . ' ' . $resourceDO->getNameAlternative();
        $content = $this->generator->generate($query);

        return $content;
    }
}