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
 * @ORM\Entity
 *  * @ORM\Entity(repositoryClass="Application\Entity\Repository\OrganizationClassificationRepository")
 * @ORM\Table(name="OrganizationClassification")
 */
class OrganizationClassification extends Common
{
    /* Property */

    /**
     * @ORM\Column(type="string", name="Code",length=50, nullable=true,unique=true)
     */
    protected $code;

    /**
     * @ORM\Column(type="string", name="Name",length=255, nullable=true)
     */
    protected $name;

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

}