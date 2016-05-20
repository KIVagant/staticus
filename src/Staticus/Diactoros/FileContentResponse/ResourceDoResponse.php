<?php
namespace Staticus\Diactoros\FileContentResponse;

use Staticus\Resources\ResourceDOInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

class ResourceDoResponse extends Response implements ResourceDoResponseInterface
{
    /**
     * @var ResourceDOInterface
     */
    protected $content;

    public function getContent()
    {
        return $this->content;
    }

    public function setContent(ResourceDOInterface $content)
    {
        $this->content = $content;
    }

    /**
     * Create an empty response with the given status code and attached resource.
     *
     * @param ResourceDOInterface $resource
     * @param int $status Status code for the response, if any.
     * @param array $headers Headers for the response, if any.
     */
    public function __construct(ResourceDOInterface $resource, $status = 204, array $headers = [])
    {
        $this->setContent($resource);
        $body = $this->createBody();
        parent::__construct($body, $status, $headers);
    }

    protected function createBody()
    {
        $stream = new Stream('php://temp', 'r');

        return $stream;
    }
}
