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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\RoleRepository")
 * @ORM\Table(name="Role")
 */
class Role extends Common
{

    /* Foreing key */
    
    /* Property */
    /**
     * @ORM\Column(type="string", name="RoleName", length=250, nullable=false, unique=true)
     *
     * @var string
     */
    protected $roleName;

    /**
     * @ORM\Column(type="string", name="Description", length=500, nullable=true)
     *
     * @var string
     */
    protected $description;

    /**
     * @return string
     */
    public function getRoleName()
    {
        return $this->roleName;
    }

    /**
     * @param string $roleName
     */
    public function setRoleName($roleName)
    {
        $this->roleName = $roleName;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /* Relationship */

    /* Getter and Setter */

}
