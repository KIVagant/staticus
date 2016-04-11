<?php
namespace Staticus\Diactoros\FileContentResponse;

use Psr\Http\Message\UploadedFileInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

class FileUploadedResponse extends Response implements FileResponseInterface
{
    use Response\InjectContentTypeTrait;
    /**
     * @var resource
     */
    protected $resource;

    /**
     * @var UploadedFileInterface
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
     * @return UploadedFileInterface
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param UploadedFileInterface $content
     */
    public function setContent($content)
    {
        if (!$content instanceof UploadedFileInterface) {
            throw new \RuntimeException('Content must be an instance of UploadedFileInterface', __LINE__);
        }
        $this->content = $content;
    }

    /**
     * Create an empty response with the given status code and attached uploaded file information.
     *
     * @param UploadedFileInterface $uploadedFile
     * @param int $status Status code for the response, if any.
     * @param array $headers Headers for the response, if any.
     */
    public function __construct(UploadedFileInterface $uploadedFile, $status = 204, array $headers = [])
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
