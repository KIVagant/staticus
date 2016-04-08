<?php
namespace Staticus\Action\Voice;

class ActionGet extends VoiceActionAbstract
{
    protected function action()
    {
        $this->response = $this->getAction();

        return $this->next();
    }
}