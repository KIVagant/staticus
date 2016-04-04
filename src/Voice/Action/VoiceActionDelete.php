<?php
namespace Voice\Action;

use Voice\Exceptions\VoiceErrorException;
use Zend\Diactoros\Response\EmptyResponse;

class VoiceActionDelete extends VoiceActionAbstract
{
    protected function action()
    {
        // HTTP 204 No content
        if (file_exists($this->voiceFilePath)) {
            if (unlink($this->voiceFilePath)) {

                return new EmptyResponse(204, static::$defaultHeaders);
            } else {
                throw new VoiceErrorException('The file cannot be removed: ' . $this->voiceFilePath);
            }
        }

        return new EmptyResponse(204, static::$defaultHeaders);
    }
}
