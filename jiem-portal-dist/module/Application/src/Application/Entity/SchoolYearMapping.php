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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\SchoolYearMappingRepository")
 * @ORM\Table(name="SchoolYearMapping")
 */
class SchoolYearMapping extends Common
{

    /* Foreing key */
    /* Property */
    /**
     * @ORM\Column(type="integer", name="SchoolYearId", nullable=false)
     *
     * @var integer
     */
    protected $schoolYearId;
    /* Property */
    /**
     * @ORM\Column(type="string", name="SchoolYearName", length=250, nullable=true)
     *
     * @var string
     */
    protected $schoolYearName;
    /**
     * @ORM\Column(type="string", name="OrgCode", length=10, nullable=true)
     *
     * @var string
     */
    protected $orgCode;

    /**
     * @return int
     */
    public function getSchoolYearId()
    {
        return $this->schoolYearId;
    }

    /**
     * @param int $schoolYearId
     */
    public function setSchoolYearId($schoolYearId)
    {
        $this->schoolYearId = $schoolYearId;
    }

    /**
     * @return string
     */
    public function getSchoolYearName()
    {
        return $this->schoolYearName;
    }

    /**
     * @param string $schoolYearName
     */
    public function setSchoolYearName($schoolYearName)
    {
        $this->schoolYearName = $schoolYearName;
    }

    /**
     * @return string
     */
    public function getOrgCode()
    {
        return $this->orgCode;
    }

    /**
     * @param string $orgCode
     */
    public function setOrgCode($orgCode)
    {
        $this->orgCode = $orgCode;
    }

    /**
     * @ORM\Column(type="string", name="SchoolYearCode", length=8, nullable=true)
     *
     * @var string
     */
    protected $schoolYearCode = '';
    
    /**
     * @return string
     */
    public function getSchoolYearCode(){
        return  $this->schoolYearCode;
    }
    
    /**
     * Setter for SchoolYearCode
     * @param string $schoolYearCode
     */
    public function setSchoolYearCode($schoolYearCode){
        $this->schoolYearCode = $schoolYearCode;
    }
    
}