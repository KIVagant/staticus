<?php
namespace Staticus\Diactoros\FileContentResponse;

use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;
use Zend\Diactoros\UploadedFile;

class FileUploadedResponse extends Response implements FileResponseInterface
{
    use Response\InjectContentTypeTrait;
    /**
     * @var resource
     */
    protected $resource;

    /**
     * @var UploadedFile
     */
    protected $content;

    /**
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return UploadedFile
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param UploadedFile $content
     */
    public function setContent($content)
    {
        if (!$content instanceof UploadedFile) {
            throw new \RuntimeException('Content must be an instance of UploadedFile');
        }
        $this->content = $content;
    }

    /**
     * Create an empty response with the given status code and attached uploaded file information.
     *
     * @param UploadedFile $uploadedFile
     * @param int $status Status code for the response, if any.
     * @param array $headers Headers for the response, if any.
     */
    public function __construct(UploadedFile $uploadedFile, $status = 204, array $headers = [])
    {
        $this->setContent($uploadedFile);
        $body = $this->createBody();
        parent::__construct($body, $status, $headers);
    }

    protected function createBody()
    {
        $stream = new Stream('php://temp', 'r');

        return $stream;
    }
}
