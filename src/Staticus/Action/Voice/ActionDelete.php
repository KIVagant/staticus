<?php
namespace Staticus\Action\Voice;

class ActionDelete extends VoiceActionAbstract
{
    protected function action()
    {
        $this->response = $this->deleteAction();

        return $this->next();
    }
}