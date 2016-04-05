<?php
namespace Staticus\Action\Fractal;

use Common\Config\Config;
use FractalManager\Manager;
use Staticus\Action\StaticActionAbstract;
use Staticus\Exceptions\ErrorException;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class FractalActionAbstract extends StaticActionAbstract
{
    protected static $defaultHeaders = [
        'Content-Type' => 'image/png',
    ];

    public function __construct(Manager $manager, Config $config)
    {
        $this->generator = $manager;
        $this->providerName = $this->getRealClassName($this->generator->getAdapter());
        $this->config = $config->get('fractal');
    }

    /**
     * @param $text
     * @param $filePath
     * @return mixed
     */
    protected function generate($text, $filePath)
    {
        $content = $this->generator->generate($text);

        return $content;
    }
    /**
     * @param $filePath
     * @param $image
     */
    protected function writeFile($filePath, $image)
    {
        if (!imagepng($image, $filePath)) {
            imagedestroy($image);
            throw new ErrorException('File cannot be written to path ' . $filePath);
        }
        imagedestroy($image);
    }
}
