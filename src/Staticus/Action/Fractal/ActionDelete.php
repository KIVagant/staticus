<?php
namespace Staticus\Action\Fractal;

class ActionDelete extends FractalActionAbstract
{
    protected function action()
    {
        return $this->deleteAction();
    }
}
