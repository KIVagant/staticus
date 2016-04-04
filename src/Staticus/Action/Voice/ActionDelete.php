<?php
namespace Staticus\Action\Voice;

class ActionDelete extends VoiceActionAbstract
{
    protected function action()
    {
        return $this->deleteAction();
    }
}
