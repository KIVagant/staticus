<?php
namespace Staticus\Middlewares;

use Staticus\Resources\Commands\DeleteSafetyResourceCommand;
use Staticus\Resources\Commands\DestroyResourceCommand;
use Staticus\Resources\PrepareResourceMiddlewareAbstract;
use Zend\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Staticus\Resources\File\ResourceDO;

abstract class ActionDeleteAbstract extends MiddlewareAbstract
{
    /**
     * @var ResourceDO
     */
    protected $resourceDO;

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

    protected function action()
    {
        $headers = [
            'Content-Type' => $this->resourceDO->getMimeType(),
        ];
        $destroy = PrepareResourceMiddlewareAbstract::getParamFromRequest('destroy', $this->request);
        if ($destroy) {
            $command = new DestroyResourceCommand($this->resourceDO);
            $command->run();
        } else {
            $command = new DeleteSafetyResourceCommand($this->resourceDO);
            $command->run();
        }

        /** @see \Zend\Diactoros\Response::$phrases */
        return new EmptyResponse(204, $headers);
    }
}