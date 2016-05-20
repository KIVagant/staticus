<?php
namespace Staticus\Auth;

use Staticus\Acl\Roles;
use Staticus\Config\ConfigInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Stratigility\MiddlewareInterface;

/**
 * http-auth layer, look into auth.global.php
 */
class AuthBasicMiddleware implements MiddlewareInterface
{
    protected $config;

    /**
     * @var User|UserInterface
     */
    protected $user;

    public function __construct(ConfigInterface $config, UserInterface $user)
    {
        $this->config = $config->get('auth.basic');
        $this->user = $user;
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
        if ($this->isAdminAuthentication($request)) {
            $this->user->addRoles([Roles::ADMIN]);
        }

        return $next($request, $response);
    }

    /**
     * @param string $login
     * @param string $pass
     * @return bool
     */
    protected function checkCredentials($login, $pass)
    {
        foreach ($this->config['users'] as $user) {
            if (array_key_exists('name', $user) && array_key_exists('pass', $user)
                && $login === $user['name']
                && $pass === $user['pass']
            ) {

                return true;
            }
        }

        return false;
    }

    /**
     * @param string $authHeader
     * @return bool
     */
    protected function checkHeader($authHeader)
    {
        if ($authHeader) {
            $authToken = str_replace('Basic ', '', $authHeader);
            foreach ($this->config['users'] as $user) {
                if (isset($user['name']) && isset($user['pass'])
                    && $authToken === base64_encode($user['name'] . ':' . $user['pass'])
                ) {

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function isAdminAuthentication(ServerRequestInterface $request)
    {
        return (
            (
                isset($_SERVER['PHP_AUTH_USER'])
                && isset($_SERVER['PHP_AUTH_PW'])
                && $this->checkCredentials($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])
            )
            || $this->checkHeader($request->getHeaderLine('authorization'))
        );
    }
}
