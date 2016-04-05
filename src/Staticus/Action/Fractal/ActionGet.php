<?php
namespace Staticus\Action\Fractal;

class ActionGet extends FractalActionAbstract
{
    protected function action()
    {
        return $this->getAction();
    }
}
