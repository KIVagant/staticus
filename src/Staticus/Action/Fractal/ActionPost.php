<?php
namespace Staticus\Action\Fractal;

class ActionPost extends FractalActionAbstract
{
    protected function action()
    {
        $this->response = $this->postAction();

        return $this->next();
    }
}