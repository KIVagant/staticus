<?php
namespace Staticus\Action;

use Staticus\Exceptions\ErrorException;
use Zend\Diactoros\Response\EmptyResponse;

class VoiceActionDelete extends VoiceActionAbstract
{
    protected function action()
    {
        // HTTP 204 No content
        if (file_exists($this->filePath)) {
            if (unlink($this->filePath)) {

                return new EmptyResponse(204, static::$defaultHeaders);
            } else {
                throw new ErrorException('The file cannot be removed: ' . $this->filePath);
            }
        }

        return new EmptyResponse(204, static::$defaultHeaders);
    }
}
