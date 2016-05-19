<?php
namespace Staticus\Acl;

use Staticus\Auth\User;
use Staticus\Auth\UserInterface;
use Staticus\Config\ConfigInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Staticus\Diactoros\FileContentResponse\ResourceDoResponse;
use Staticus\Exceptions\WrongRequestException;
use Staticus\Resources\ResourceDOInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Stratigility\MiddlewareInterface;

class AclMiddleware implements MiddlewareInterface
{
    protected $config;

    /**
     * @var AclServiceInterface|AclService
     */
    protected $service;

    /**
     * @var UserInterface|User
     */
    protected $user;

    public function __construct(ConfigInterface $config, AclServiceInterface $service, UserInterface $user)
    {
        $this->config = $config->get('acl');
        $this->service = $service;
        $this->user = $user;
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
        $resourceNamespace = $resourceDO->getNamespace();
        $userNamespace = $this->user->getNamespace();
        $AclResourceCommon = get_class($resourceDO);
        $AclResourceUnique = $resourceDO instanceof ResourceInterface
            ? $resourceDO->getResourceId()
            : null;

        if (
            // User have access to this type of resources regardless namespaces
            $this->isAllowed($AclResourceCommon, $action, '')

            // User have access to this unique resource regardless namespaces
            || $this->isAllowed($AclResourceUnique, $action, '')

            // User have access to this resource type in common namespace
            || (
                !$resourceNamespace
                && $this->isAllowed($AclResourceCommon, $action, ResourceDOInterface::NAMESPACES_WILDCARD)
                )

            // User have access to this resource type in concrete selected namespace
            || (
                $resourceNamespace
                && $this->isAllowed($AclResourceCommon, $action, $resourceNamespace)
            )
            || (
                // This is a user home namespace
                $resourceNamespace === $userNamespace

                // User have access to the current action in his own namespace
                && $this->isAllowed($AclResourceCommon, $action, UserInterface::NAMESPACES_WILDCARD)
            )
            || (
                // This is an another user namespace
                $resourceNamespace !== $userNamespace
                && 0 === strpos($resourceNamespace, UserInterface::NAMESPACES)

                // User have access to the current action in his own namespace
                && $this->isAllowedForGuest($AclResourceCommon, $action, UserInterface::NAMESPACES_WILDCARD)
            )
        ) {

            return $next($request, $response);
        }

        return new EmptyResponse(403);
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

    protected function isAllowed($aclResource, $action, $namespace = '')
    {
        if (!$this->service->acl()->hasResource($namespace . $aclResource)) {

            return false;
        }

        return $this->user->can($namespace . $aclResource, $action);
    }

    protected function isAllowedForGuest($aclResource, $action, $namespace = '')
    {
        if (!$this->service->acl()->hasResource($namespace . $aclResource)) {

            return false;
        }

        return $this->service->acl()->isAllowed(Roles::GUEST, $namespace . $aclResource, $action);
    }
}