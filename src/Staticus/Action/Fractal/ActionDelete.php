<?php
namespace Staticus\Action\Fractal;

class ActionDelete extends FractalActionAbstract
{
    protected function action()
    {
        $this->response = $this->deleteAction();

        return $this->next();
    }
}