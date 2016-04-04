<?php
namespace App\Auth;

use Common\Config\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Expressive\Router\RouterInterface;
use Zend\Stratigility\MiddlewareInterface;

/**
 * Слой авторизации, см. конфиг auth.global.php
 * Class AuthBasicMiddleware
 * @package App\Auth
 */
class AuthBasicMiddleware implements MiddlewareInterface
{
    protected $config;
    protected $router;

    public function __construct(RouterInterface $router, Config $config)
    {
        $this->router = $router;
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
        $match = $this->router->match($request);
        if (in_array($match->getMatchedMiddleware(), $this->config['middlewares'])) {
            $authToken = str_replace('Basic ', '', $request->getHeaderLine('authorization'));
            foreach ($this->config['users'] as $user) {
                if (base64_encode($user) == $authToken) {

                    return $next($request, $response);
                }
            }

            return new EmptyResponse(401, ['WWW-Authenticate' => 'Basic realm="My Realm"']);
        }

        return $next($request, $response);
    }
}
