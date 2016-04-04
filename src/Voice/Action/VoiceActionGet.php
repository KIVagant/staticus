<?php
namespace Voice\Action;

use Zend\Diactoros\Response\EmptyResponse;

class VoiceActionGet extends VoiceActionAbstract
{
    protected function action()
    {
        if (file_exists($this->voiceFilePath)) {

            return $this->XAccelRedirect($this->voiceFilePath);
        }

        return new EmptyResponse(404, static::$defaultHeaders);
    }
}
