<?php
namespace Staticus\Acl;

use Staticus\Config\ConfigInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Staticus\Diactoros\FileContentResponse\ResourceDoResponse;
use Staticus\Exceptions\WrongRequestException;
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
        $this->checkResponseOrDie($response);

//        return new \Zend\Diactoros\Response\JsonResponse(
//            $this->service->acl()->isAllowed(
//                Roles::USER,
//                \Staticus\Resources\Jpg\ResourceDO::class,
//                Actions::ACTION_DELETE));
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
}