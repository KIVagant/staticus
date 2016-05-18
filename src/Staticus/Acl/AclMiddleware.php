<?php
namespace Staticus\Acl;

use Staticus\Config\ConfigInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Staticus\Diactoros\FileContentResponse\ResourceDoResponse;
use Staticus\Exceptions\WrongRequestException;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Stratigility\MiddlewareInterface;

class AclMiddleware implements MiddlewareInterface
{
    protected $config;
    protected $service;

    public function __construct(ConfigInterface $config, AclService $service)
    {
        $this->config = $config->get('acl');
        $this->service = $service;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    )
    {
        /** @var $response ResourceDoResponse */
        $this->checkResponseOrDie($response);

        $method = $request->getMethod();
        $action = $this->getAction($method);
        $resourceDO = $response->getContent();
        $AclResourceCommon = get_class($resourceDO);
        $AclResourceUnique = $resourceDO instanceof ResourceInterface
            ? $resourceDO->getResourceId()
            : null;
        $role = Roles::GUEST;// @todo get roles from authenticated user
        if (!$this->isAllowed($role, $AclResourceCommon, $action)) {

            return new EmptyResponse(403);
        }
        if (!$this->isAllowed($role, $AclResourceUnique, $action)) {

            return new EmptyResponse(403);
        }

        return $next($request, $response);
    }


    /**
     * @param ResponseInterface $response
     */
    protected function checkResponseOrDie(ResponseInterface $response)
    {
        if (!$this->isSupportedResponse($response)) {

            // something like PrepareResourceMiddleware should be called before this
            throw new WrongRequestException(
                'Unsupported type of the response for ACL. Resource preparing layer must be called before this.',
                __LINE__);
        }
    }
    /**
     * @param ResponseInterface $response
     * @return bool
     */
    protected function isSupportedResponse(ResponseInterface $response)
    {
        return $response instanceof ResourceDoResponse;
    }

    /**
     * @param $method
     * @return string
     */
    protected function getAction($method)
    {
        switch ($method) {
            case 'GET':
                $action = Actions::ACTION_READ;
                break;
            case 'POST':
                $action = Actions::ACTION_WRITE;
                break;
            case 'DELETE':
                $action = Actions::ACTION_DELETE;
                break;
            default:
                throw new WrongRequestException(
                    'Unknown access control action',
                    __LINE__);
        }

        return $action;
    }

    protected function isAllowed($role, $AclResourceName, $action)
    {
        return (
            !$this->service->acl()->hasRole($role)
            || !$this->service->acl()->hasResource($AclResourceName)
            || $this->service->acl()->isAllowed(
                $role,
                $AclResourceName,
                $action)
        );
    }
}