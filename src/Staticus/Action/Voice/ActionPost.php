<?php
namespace Staticus\Action\Voice;

class ActionPost extends VoiceActionAbstract
{
    protected function action()
    {
        return $this->postAction();
    }
}
