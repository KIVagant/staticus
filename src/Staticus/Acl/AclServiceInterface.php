<?php
namespace Staticus\Acl;

interface AclServiceInterface
{
    public function fillRoles(array $rolesConfig);
    public function fillResources(array $resourcesConfig);

    /**
     * @return \Zend\Permissions\Acl\AclInterface
     */
    public function acl();
}