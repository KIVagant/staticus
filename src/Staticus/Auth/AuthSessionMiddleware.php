<?php
namespace Staticus\Auth;

use Staticus\Config\ConfigInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Stratigility\MiddlewareInterface;

class AuthSessionMiddleware implements MiddlewareInterface
{
    protected $config;
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config->get('auth.session');
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
