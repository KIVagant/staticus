<?php
namespace Staticus\Auth;

use Staticus\Config\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Stratigility\MiddlewareInterface;

/**
 * Слой авторизации, см. конфиг auth.global.php
 * Class AuthBasicMiddleware
 * @package Staticus\Auth
 */
class AuthBasicMiddleware implements MiddlewareInterface
{
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config->get('auth.basic');
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
        $authToken = str_replace('Basic ', '', $request->getHeaderLine('authorization'));
        foreach ($this->config['users'] as $user) {
            if (isset($user['name']) && isset($user['pass'])
                && (
                (
                    isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])
                    && $_SERVER['PHP_AUTH_USER'] == $user['name']
                    && $_SERVER['PHP_AUTH_PW'] == $user['pass']
                )
                || base64_encode($user['name'] . ':' . $user['pass']) == $authToken
                )) {

                return $next($request, $response);
            }
        }

        return new EmptyResponse(401, ['WWW-Authenticate' => 'Basic realm="Staticus"']);
    }
}