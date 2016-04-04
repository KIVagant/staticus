<?php

namespace Common\Response;


use Zend\Stdlib\ResponseInterface;

class XAccellResponse implements ResponseInterface
{

    /**
     * Set metadata
     *
     * @param  string|int|array|\Traversable $spec
     * @param  mixed $value
     */
    public function setMetadata($spec, $value = null)
    {
        // TODO: Implement setMetadata() method.
    }

    /**
     * Get metadata
     *
     * @param  null|string|int $key
     * @return mixed
     */
    public function getMetadata($key = null)
    {
        // TODO: Implement getMetadata() method.
    }

    /**
     * Set content
     *
     * @param  mixed $content
     * @return mixed
     */
    public function setContent($content)
    {
        // TODO: Implement setContent() method.
    }

    /**
     * Get content
     *
     * @return mixed
     */
    public function getContent()
    {
        // TODO: Implement getContent() method.
    }
}