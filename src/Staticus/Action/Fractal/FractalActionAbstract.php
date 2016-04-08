<?php
namespace Staticus\Action\Fractal;

use Common\Config\Config;
use FractalManager\Manager;
use Staticus\Action\StaticActionAbstract;

abstract class FractalActionAbstract extends StaticActionAbstract
{
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
}