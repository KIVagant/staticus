<?php

namespace Staticus\Auth\SaveHandlers;

use Zend\Session\SaveHandler\SaveHandlerInterface;

/**
 * Session save handler for redis.io
 */
class Redis implements SaveHandlerInterface
{

    /**
     * @var \Redis
     */
    protected $redis;

    /**
     * Session Save Path
     *
     * @var string
     */
    protected $sessionSavePath;

    /**
     * Session Name
     *
     * @var string
     */
    protected $sessionName;

    /**
     * Lifetime
     * @var int
     */
    protected $lifetime;

    /**
     * Constructor
     * @param string $host
     * @param string $port
     * @param string $password
     */
    public function __construct($host, $port, $password)
    {
        $this->redis = new \Redis();
        $this->redis->connect($host, $port);
        $this->redis->auth($password);
    }

    /**
     * Open Session - retrieve resources
     *
     * @param string $savePath
     * @param string $name
     */
    public function open($savePath, $name)
    {
        $this->sessionSavePath = $savePath;
        $this->sessionName = $name;
        $this->lifetime = ini_get('session.gc_maxlifetime');
    }

    /**
     * Close Session - free resources
     *
     */
    public function close()
    {
        $this->redis->close();
    }

    /**
     * Read session data
     *
     * @param string $identifier
     * @return bool|string
     */
    public function read($identifier)
    {
        return $this->redis->get($this->getSessionKey($identifier));
    }

    /**
     * Read sessions for user
     * @param $userId
     * @return array
     */
    public function readByUser($userId)
    {
        return $this->redis->sMembers($this->getUserKey($userId));
    }

    /**
     * Write Session - commit data to resource
     *
     * @param string $identifier
     * @param mixed $data
     */
    public function write($identifier, $data)
    {
        $this->redis->setex(
            $this->getSessionKey($identifier),
            $this->lifetime,
            $data
        );
    }

    /**
     * Write session ids for user
     * @param $sessionId
     * @param $userId
     */
    public function writeByUser($sessionId, $userId)
    {
        $this->redis->sAdd($this->getUserKey($userId), $sessionId);
    }

    /**
     * Destroy Session and remove data from user's container
     * @param string $sessionId
     * @param null $userId
     */
    public function destroy($sessionId, $userId = null)
    {
        $sessionKey = $this->getSessionKey($sessionId);
        if ($this->redis->exists($sessionKey)) {
            $this->redis->delete($sessionKey);
        }

        /* Remove session id from user's container */
        if (null !== $userId) {
            $userKey = $this->getUserKey($userId);
            if ($this->redis->sIsMember($userKey, $sessionId)) {
                $this->redis->sRemove($userKey, $sessionId);
            }
        }
    }

    /**
     * Destroy all sessions by user
     * @param $userId
     */
    public function destroyByUser($userId)
    {
        foreach ($this->readByUser($userId) as $sessionId) {
            $this->destroy($sessionId);
        }

        $userKey = $this->getUserKey($userId);
        if ($this->redis->exists($userKey)) {
            $this->redis->delete($userKey);
        }
    }

    /**
     * Add prefix for session's key
     * @param $sessionId
     * @return string
     */
    protected function getSessionKey($sessionId)
    {
        return 'session_' . $sessionId;
    }

    /**
     * Add prefix for user's key
     * @param $userId
     * @return string
     */
    protected function getUserKey($userId)
    {
        return 'user_' . $userId;
    }

    /**
     * Garbage Collection - remove old session data older
     * than $maxlifetime (in seconds)
     *
     * @param int $maxlifetime
     */
    public function gc($maxlifetime)
    {
        // TODO: Implement gc() method.
    }
}
