<?php
namespace App\Diactoros\FileContentResponse;

use Zend\Diactoros\HeaderSecurity;
use Zend\Diactoros\MessageTrait;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

/**
 * A class representing HTTP responses with body.
 */
class FileContentResponse extends Response
{
    use MessageTrait;
    /**
     * @var resource
     */
    protected $resource;
    /**
     * @var string
     */
    protected $path;

    /**
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Create an empty response with the given status code.
     *
     * @param string $path
     * @param null $content
     * @param int $status Status code for the response, if any.
     * @param array $headers Headers for the response, if any.
     */
    public function __construct($path = '', $content = null, $status = 204, array $headers = [])
    {
        $this->path = $path;
        $body = $this->createBody($content);
        parent::__construct($body, $status, $headers);
    }

    protected function createBody($content)
    {
        if (is_resource($content)) {
            $this->resource = $content;
            $content = null;
        }
        if ($content instanceof StreamInterface) {

            return $content;
        } elseif (null === $content || false === $content || '' === $content) { // but not zero
            $body = new Stream('php://temp', 'r');

            return $body;
        }

        $body = new Stream('php://temp', 'wb+');
        $body->write((string)$content);
        $body->rewind();

        return $body;
    }

    /**
     * @param $headers
     * @see \Zend\Diactoros\Response::__construct
     */
    public function setHeaders($headers)
    {
        list($this->headerNames, $headers) = $this->filterHeaders($headers);
        $this->assertHeaders($headers);
        $this->headers = $headers;
    }
    /**
     * Ensure header names and values are valid.
     *
     * @param array $headers
     * @throws InvalidArgumentException
     * @see \Zend\Diactoros\Response::assertHeaders
     */
    private function assertHeaders(array $headers)
    {
        foreach ($headers as $name => $headerValues) {
            HeaderSecurity::assertValidName($name);
            array_walk($headerValues, '\Zend\Diactoros\HeaderSecurity::assertValid');
        }
    }
}
