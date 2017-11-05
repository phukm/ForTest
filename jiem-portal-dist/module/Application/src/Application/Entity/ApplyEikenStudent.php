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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\ApplyEikenStudentRepository")
 * @ORM\Table(name="ApplyEikenStudent")
 */
class ApplyEikenStudent extends Common {
    /* Foreing key */

    /* Property */

    /**
     * @ORM\Column(type="string", name="OrgNo", length=50, nullable=false)
     * 
     */
    protected $orgNo;

    /**
     * @ORM\Column(type="integer", name ="ApplyEikenOrgId", nullable=true)
     *
     * @var integer
     */
    protected $applyEikenOrgId;

    /**
     * Foreing key reference to OrgSchoolYear
     * @ORM\Column(type="integer", name="OrgSchoolYearId", nullable=true)
     *
     * @var integer
     */
    protected $orgSchoolYearId;
    
    /**
     * Foreing key reference to SchoolYear
     * @ORM\Column(type="integer", name="SchoolYearId", nullable=true)
     *
     * @var integer
     */
    protected $schoolYearId;

    /**
     * Foreing key reference to EikenLevel
     * @ORM\Column(type="integer", name="EikenLevelId", nullable=true)
     *
     * @var integer
     */
    protected $eikenLevelId;

    /**
     * Foreing key reference to EikenLevel
     * @ORM\Column(type="integer", name="TotalStudent", nullable=true)
     *
     * @var integer
     */
    protected $totalStudent;

    /**
     * Foreing key reference to EikenLevel
     * @ORM\Column(type="integer", name="isDiscount", nullable=true)
     *
     * @var integer
     */
    protected $isDiscount;
    /**
     *
     * @return int
     */
    public function getOrgNo()
    {
        return $this->orgNo;
    }

    /**
     *
     * @param string $orgNo            
     */
    public function setOrgNo($orgNo)
    {
        $this->orgNo = $orgNo;
    }
    
    /**
     *
     * @return int
     */
    public function getApplyEikenOrgId()
    {
        return $this->applyEikenOrgId;
    }

    /**
     *
     * @param int $applyEikenOrgId            
     */
    public function setApplyEikenOrgId($applyEikenOrgId)
    {
        $this->applyEikenOrgId = $applyEikenOrgId;
    }
    /**
     *
     * @return int
     */
    public function getOrgSchoolYearId()
    {
        return $this->orgSchoolYearId;
    }

    /**
     *
     * @param int $orgSchoolYearId            
     */
    public function setOrgSchoolYearId($orgSchoolYearId)
    {
        $this->orgSchoolYearId = $orgSchoolYearId;
    }
    /**
     *
     * @return int
     */
    public function getEikenLevelId()
    {
        return $this->eikenLevelId;
    }

    /**
     *
     * @param int $eikenLevelId            
     */
    public function setEikenLevelId($eikenLevelId)
    {
        $this->eikenLevelId = $eikenLevelId;
    }
    /**
     *
     * @return int
     */
    public function getTotalStudent()
    {
        return $this->totalStudent;
    }

    /**
     *
     * @param int $totalStudent            
     */
    public function setTotalStudent($totalStudent)
    {
        $this->totalStudent = $totalStudent;
    }
    /**
     *
     * @return int
     */
    public function getIsDiscount()
    {
        return $this->isDiscount;
    }

    /**
     *
     * @param int $isDiscount            
     */
    public function setIsDiscount($isDiscount)
    {
        $this->isDiscount = $isDiscount;
    }
    
    function getSchoolYearId() {
        return $this->schoolYearId;
    }

    function setSchoolYearId($schoolYearId) {
        $this->schoolYearId = $schoolYearId;
    }


}
