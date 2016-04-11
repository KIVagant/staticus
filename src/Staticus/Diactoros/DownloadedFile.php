<?php
namespace Staticus\Diactoros;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Staticus\Resources\Exceptions\SaveResourceErrorException;
use Zend\Diactoros\Stream;

/**
 * Special object for files, downloaded with curl, not from HTTP POST
 */
class DownloadedFile implements UploadedFileInterface
{
    protected $file;
    protected $size;
    protected $moved = false;
    protected $stream;
    private $error;
    private $clientFilename;
    private $clientMediaType;

    public function __construct($file, $size, $error, $clientFilename = null, $clientMediaType = null)
    {
        $this->file = $file;
        $this->size = $size;
        $this->error = $error;
        $this->clientFilename = $clientFilename;
        $this->clientMediaType = $clientMediaType;
    }

    public function moveTo($targetPath)
    {
        if (! is_string($targetPath)) {
            throw new SaveResourceErrorException(
                'Invalid path provided for move operation; must be a string', __LINE__
            );
        }

        if (empty($targetPath)) {
            throw new SaveResourceErrorException(
                'Invalid path provided for move operation; must be a non-empty string', __LINE__
            );
        }

        if ($this->moved) {
            throw new SaveResourceErrorException('Cannot move file; already moved!', __LINE__);
        }

        if (false === rename($this->file, $targetPath)) {
            throw new SaveResourceErrorException('Error occurred while moving downloaded file', __LINE__);
        }
        $this->moved = true;
    }

    public function getStream()
    {
        if ($this->error !== UPLOAD_ERR_OK) {
            throw new SaveResourceErrorException('Cannot retrieve stream due to upload error', __LINE__);
        }

        if ($this->moved) {
            throw new SaveResourceErrorException('Cannot retrieve stream after it has already been moved', __LINE__);
        }

        if ($this->stream instanceof StreamInterface) {
            return $this->stream;
        }

        $this->stream = new Stream($this->file);
        return $this->stream;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getClientFilename()
    {
        return $this->clientFilename;
    }

    public function getClientMediaType()
    {
        return $this->clientMediaType;
    }
}