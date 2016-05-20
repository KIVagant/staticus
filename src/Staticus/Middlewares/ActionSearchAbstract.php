<?php
namespace Staticus\Middlewares;

use Staticus\Resources\ResourceDOInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Staticus\Resources\File\ResourceDO;
use Zend\Diactoros\Response\JsonResponse;

abstract class ActionSearchAbstract extends MiddlewareAbstract
{
    /**
     * @var ResourceDO
     */
    protected $resourceDO;

    /**
     * Search provider
     * @var mixed
     */
    protected $searcher;

    public function __construct(
        ResourceDOInterface $resourceDO, $generator)
    {
        $this->resourceDO = $resourceDO;
        $this->searcher = $generator;
    }

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
        parent::__invoke($request, $response, $next);
        $this->response = $this->action();

        return $this->next();
    }

    abstract protected function search(ResourceDOInterface $resourceDO);

    protected function action()
    {
        $response = $this->search($this->resourceDO);

        return new JsonResponse(['found' => $response]);
    }
}