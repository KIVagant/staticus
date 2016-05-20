<?php
namespace Staticus\Auth;

use Staticus\Acl\AclServiceInterface;
use Staticus\Acl\Roles;
use Zend\Permissions\Acl\AclInterface;
use Zend\Permissions\Acl\Acl;

class User implements UserInterface
{
    protected $id;
    protected $roles = [];
    protected $namespace = '';

    /**
     * @var Acl|AclInterface
     */
    protected $acl;

    public function __construct(AclServiceInterface $acl, array $roles = [])
    {
        $this->addRoles($roles);
        $this->acl = $acl->acl();
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->id !== null;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    public function login($userId, array $roles)
    {
        if (!$userId) {
            throw new Exceptions\RuntimeException('User Id cannot be empty', __LINE__);
        }
        $this->id = $userId;
        $this->addRoles($roles);
    }

    public function logout()
    {
        $this->id = null;
        $this->roles = $this->getDefaultRoles();
    }
    /**
     * @param \Zend\Permissions\Acl\Resource\ResourceInterface|string $resource
     * @param string $action
     * @return bool
     */
    public function can($resource, $action)
    {
        foreach ($this->roles as $role) {
            if ($this->acl->isAllowed($role, $resource, $action)) {

                return true;
            }
        }

        return false;
    }

    public function addRoles(array $roles)
    {
        $this->roles = array_unique(array_merge($this->getDefaultRoles(), $roles));
    }

    /**
     * @param $role
     * @return bool
     */
    public function removeRole($role)
    {
        if(($key = array_search($role, $this->roles, true)) !== false) {
            unset($this->roles[$key]);

            return true;
        }
        if (empty($this->roles)) {
            $this->roles = $this->getDefaultRoles();
        }

        return false;
    }

    /**
     * @return array
     */
    protected function getDefaultRoles()
    {
        return [
            Roles::GUEST,
        ];
    }

    /**
     * @param string $role
     * @return bool
     */
    public function hasRole($role)
    {

        return in_array($role, $this->roles, true);
    }

    /**
     * Get the home namespace for this user
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Set the home namespace for this user
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }
}