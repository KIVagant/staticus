<?php
namespace Staticus\Action\Voice;

use Staticus\Action\Voice\VoiceActionAbstract;
use Zend\Diactoros\Response\EmptyResponse;

class VoiceActionGet extends VoiceActionAbstract
{
    protected function action()
    {
        if (file_exists($this->filePath)) {

            return $this->XAccelRedirect($this->filePath);
        }

        return new EmptyResponse(404, static::$defaultHeaders);
    }
}
