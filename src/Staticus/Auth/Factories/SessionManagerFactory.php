<?php
namespace Staticus\Auth\Factories;

use Staticus\Auth\SaveHandlers\Redis;
use Staticus\Config\ConfigInterface;
use Zend\Session\Config\SessionConfig;
use Zend\Session\SessionManager;

class SessionManagerFactory
{
    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config->get('auth.session');
    }

    public function __invoke()
    {
        $sessionConfig = new SessionConfig();
        $sessionConfig->setOptions($this->config['options']);
        $saveHandler  = new Redis(
            $this->config['redis']['host'],
            $this->config['redis']['port'],
            $this->config['redis']['password']
        );

        $sessionManager = new SessionManager($sessionConfig);
        $sessionManager->setSaveHandler($saveHandler);
        $sessionManager->start();

        return $sessionManager;
    }
}
