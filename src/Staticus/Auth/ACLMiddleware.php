<?php
namespace Staticus\Auth;

use Staticus\Config\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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
        return $next($request, $response);
    }
}
