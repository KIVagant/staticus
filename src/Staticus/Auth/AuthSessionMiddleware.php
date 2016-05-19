<?php
namespace Staticus\Auth;

use Staticus\Acl\Roles;
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

    /**
     * @var UserInterface|User
     */
    protected $user;

    public function __construct(ConfigInterface $config, ManagerInterface $manager, UserInterface $user)
    {
        $this->config = $config->get('auth.session');
        $this->manager = $manager;
        $this->user = $user;
    }
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    )
    {
        if (array_key_exists('Zend_Auth', $_SESSION)) {

            /** @var \Zend\Stdlib\ArrayObject $auth */
            $auth = $_SESSION['Zend_Auth'];
            if ($auth->offsetExists('storage')) {

                /** @var StdClass $storage */
                $storage = $auth->storage;
                if (property_exists($storage, 'user_id')) {
                    $this->user->login($storage->user_id, [Roles::USER]);
                    $this->user->setNamespace(UserInterface::NAMESPACES . DIRECTORY_SEPARATOR . $storage->user_id);
                }
            }
        }

        return $next($request, $response);
    }
}
