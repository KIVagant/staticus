<?php
namespace Staticus\Action\Voice;

class ActionPost extends VoiceActionAbstract
{
    protected function action()
    {
        $this->response = $this->postAction();

        return $this->next();
    }
}