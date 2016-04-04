<?php
namespace Staticus\Action\Fractal;

class ActionPost extends FractalActionAbstract
{
    protected function action()
    {
        return $this->postAction();
    }
}
