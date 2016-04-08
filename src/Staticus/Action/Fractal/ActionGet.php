<?php
namespace Staticus\Action\Fractal;

class ActionGet extends FractalActionAbstract
{
    protected function action()
    {
        $this->response = $this->getAction();

        return $this->next();
    }
}