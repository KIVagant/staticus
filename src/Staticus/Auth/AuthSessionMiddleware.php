<?php
namespace Staticus\Auth;

use Staticus\Config\ConfigInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Session\ManagerInterface;
use Zend\Session\SessionManager;
use Zend\Stratigility\MiddlewareInterface;

class AuthSessionMiddleware implements MiddlewareInterface
{
    protected $config;

    /**
     * @var ManagerInterface|SessionManager
     */
    protected $manager;

    public function __construct(ConfigInterface $config, ManagerInterface $manager)
    {
        $this->config = $config->get('auth.session');
        $this->manager = $manager;
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
