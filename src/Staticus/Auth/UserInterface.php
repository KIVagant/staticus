<?php
namespace Staticus\Auth;

interface UserInterface
{
    /**
     * @return bool
     */
    public function isLoggedIn();

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return array
     */
    public function getRoles();
    public function login($userId, array $roles);
    public function logout();

    /**
     * @param \Zend\Permissions\Acl\Resource\ResourceInterface|string $resource
     * @param string $action
     * @return bool
     */
    public function can($resource, $action);
    public function addRoles(array $roles);
    public function hasRole($role);

    /**
     * @param $role
     * @return bool
     */
    public function removeRole($role);
}