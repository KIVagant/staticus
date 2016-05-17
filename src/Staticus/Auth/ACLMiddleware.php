<?php
namespace Staticus\Auth;

use Staticus\Config\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Staticus\Diactoros\FileContentResponse\ResourceDoResponse;
use Staticus\Exceptions\WrongRequestException;
use Zend\Stratigility\MiddlewareInterface;

/**
 * Class ACLMiddleware
 * @package Staticus\Auth
 */
class ACLMiddleware implements MiddlewareInterface
{
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config->get('auth.acl');
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    )
    {
        $this->checkResponseOrDie($response);

        return $next($request, $response);
    }


    /**
     * @param ResponseInterface $response
     */
    protected function checkResponseOrDie(ResponseInterface $response)
    {
        if (!$this->isSupportedResponse($response)) {

            // something like PrepareResourceMiddleware should be called before this
            throw new WrongRequestException('Unsupported type of the response for ACL. Resource preparing layer must be called before this.');
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
