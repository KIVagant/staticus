<?php
namespace Staticus\Action\Voice;

use Zend\Diactoros\Response\EmptyResponse;

class ActionGet extends VoiceActionAbstract
{
    protected function action()
    {
        return $this->getAction();
    }
}
