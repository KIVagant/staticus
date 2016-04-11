<?php
namespace Staticus\Middlewares;

use Zend\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Stratigility\MiddlewareInterface;

abstract class MiddlewareAbstract implements MiddlewareInterface
{
    /**
     * @var ServerRequestInterface
     */
    protected $request;
    /**
     * @var ResponseInterface
     */
    protected $response;
    /**
     * @var callable
     */
    protected $next;

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable|null $next
     * @return EmptyResponse
     * @throws \Exception
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    )
    {
        $this->request = $request;
        $this->response = $response;
        $this->next = $next;
    }

    /**
     * @return mixed
     */
    protected function next()
    {
        $next = $this->next;

        return $next($this->request, $this->response);
    }
}
