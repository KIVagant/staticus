<?php
namespace Staticus\Acl;

use Staticus\Config\ConfigInterface;
use Zend\Permissions\Acl\AclInterface;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;

class AclService implements AclServiceInterface
{
    const ROLES = 'roles';
    const RESOURCES = 'resources';
    const PRIVILEGES = 'privileges';
    const INHERIT = 'inherit';

    protected $config;

    /**
     * @var AclInterface|Acl
     */
    protected $acl;

    public function __construct(ConfigInterface $config, AclInterface $acl)
    {
        $this->config = $config->get('acl');
        $this->acl = $acl;
        $this->fillRoles($this->config[self::ROLES]);
        $this->fillResources($this->config[self::RESOURCES]);
    }

    public function fillRoles(array $rolesConfig)
    {
        foreach ($rolesConfig as $role => $options) {
            $inherit = $this->getOption($options, self::INHERIT);
            if (null !== $inherit
                && !is_string($inherit)
                && !is_array($inherit)
                && !$inherit instanceof RoleInterface
            ) {
                throw new Exceptions\RuntimeException(
                    'Inherit option must be a string, an array or implement RoleInterface for roles', __LINE__);
            }
            $this->acl->addRole($role, $inherit);
        }
    }

    public function fillResources(array $resourcesConfig)
    {
        foreach ($resourcesConfig as $resource => $options) {
            $inherit = $this->getOption($options, self::INHERIT);
            if (null !== $inherit
                && !is_string($inherit)
                && !$inherit instanceof ResourceInterface
            ) {
                throw new Exceptions\RuntimeException(
                    'Inherit option must be a string or implement ResourceInterface for resources', __LINE__);
            }
            $this->acl->addResource($resource, $inherit);
            $privileges = $this->getOption($options, self::PRIVILEGES, []);
            foreach ($privileges as $role => $actions) {
                $this->acl->allow([$role], [$resource], $actions);
            }
        }
    }

    /**
     * @param array $options
     * @param mixed $option
     * @param mixed $default
     * @return array
     */
    protected function getOption(array $options, $option, $default = null)
    {
        $inherit = array_key_exists($option, $options)
            ? $options[$option]
            : $default;

        return $inherit;
    }

    public function acl()
    {
        return $this->acl;
    }
}
