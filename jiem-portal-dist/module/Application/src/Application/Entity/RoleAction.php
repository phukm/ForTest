<?php
/**
 * Dantai Portal (http://dantai.com.jp/)
 *
 * @link      https://fhn-svn.fsoft.com.vn/svn/FSU1.GNC.JIEM-Portal/trunk/Development/SourceCode for the source repository
 * @copyright Copyright (c) 2015 FPT-Software. (http://www.fpt-software.com)
 */
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\RoleActionRepository")
 * @ORM\Table(name="RoleAction")
 */
class RoleAction extends Common
{

    /* Foreing key */
    /**
     * @ORM\Column(type="integer", name="RoleId", nullable=true)
     *
     * @var integer
     */
    protected $roleId;

    /**
     * @ORM\Column(type="integer", name="ActionId", nullable=true)
     *
     * @var integer
     */
    protected $actionId;

    /* Property */
    /* Relationship */
    /**
     * @ORM\ManyToOne(targetEntity="Action")
     * @ORM\JoinColumn(name="ActionId", referencedColumnName="id")
     */
    protected $action;

    /**
     * @ORM\ManyToOne(targetEntity="Role")
     * @ORM\JoinColumn(name="RoleId", referencedColumnName="id")
     */
    protected $role;

    /**
     *
     * @return int
     */
    public function getRoleId()
    {
        return $this->roleId;
    }

    /**
     *
     * @param int $roleId            
     */
    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;
    }

    /**
     *
     * @return int
     */
    public function getActionId()
    {
        return $this->actionId;
    }

    /**
     *
     * @param int $actionId            
     */
    public function setActionId($actionId)
    {
        $this->actionId = $actionId;
    }

    /**
     *
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     *
     * @param mixed $action            
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     *
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     *
     * @param mixed $role            
     */
    public function setRole($role)
    {
        $this->role = $role;
    }
    /* Getter and Setter */
}
