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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\InquiryMeasureRepository")
 * @ORM\Table(name="InquiryMeasure")
 */
class InquiryMeasure extends Common
{
    /**
     * Foreing key reference to EikenLevel
     * @ORM\Column(type="integer", name="EikenLevelId", nullable=true)
     *
     * @var integer
     */
    protected $eikenLevelId;
    /**
     * Foreing key reference to OrgSchoolYear
     * @ORM\Column(type="integer", name="OrgSchoolYearId", nullable=true)
     *
     * @var integer
     */
    protected $orgSchoolYearId;
    /**
     * Foreing key reference to Organization
     * @ORM\Column(type="integer", name="OrganizationId", nullable=true)
     *
     * @var integer
     */
    protected $organizationId;
    /**
     * Foreing key reference to ClassJ
     * @ORM\Column(type="integer", name="ClassId", nullable=true)
     *
     * @var integer
     */
    protected $classId;
    /**
     * @ORM\Column(type="date", name="InquiryDate", nullable=true)
     */
    protected $inquiryDate;
      /**
     * @ORM\Column(type="string", name="MeasureTime",length=100,  nullable=true)
     *
     * @var string
     */
    protected $measureTime = '';
     /**
     * @ORM\Column(type="integer", name="Pass", nullable=true)
     *
     * @var integer
     */
    protected $pass;// (Number of pupil for each measure time)
     /**
     * @ORM\Column(type="integer", name="Fail", nullable=true)
     *
     * @var string
     */
    protected $fail;

    /**
     * @return int
     */
    public function getEikenLevelId()
    {
        return $this->eikenLevelId;
    }

    /**
     * @param int $eikenLevelId
     */
    public function setEikenLevelId($eikenLevelId)
    {
        $this->eikenLevelId = $eikenLevelId;
    }

    /**
     * @return int
     */
    public function getOrgSchoolYearId()
    {
        return $this->orgSchoolYearId;
    }

    /**
     * @param int $orgSchoolYearId
     */
    public function setOrgSchoolYearId($orgSchoolYearId)
    {
        $this->orgSchoolYearId = $orgSchoolYearId;
    }

    /**
     * @return int
     */
    public function getOrganizationId()
    {
        return $this->organizationId;
    }

    /**
     * @param int $organizationId
     */
    public function setOrganizationId($organizationId)
    {
        $this->organizationId = $organizationId;
    }

    /**
     * @return int
     */
    public function getClassId()
    {
        return $this->classId;
    }

    /**
     * @param int $classId
     */
    public function setClassId($classId)
    {
        $this->classId = $classId;
    }

    /**
     * @return mixed
     */
    public function getInquiryDate()
    {
        return $this->inquiryDate;
    }

    /**
     * @param mixed $inquiryDate
     */
    public function setInquiryDate($inquiryDate)
    {
        $this->inquiryDate = $inquiryDate;
    }

    /**
     * @return string
     */
    public function getMeasureTime()
    {
        return $this->measureTime;
    }

    /**
     * @param string $measureTime
     */
    public function setMeasureTime($measureTime)
    {
        $this->measureTime = $measureTime;
    }

    /**
     * @return int
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * @param int $pass
     */
    public function setPass($pass)
    {
        $this->pass = $pass;
    }

    /**
     * @return string
     */
    public function getFail()
    {
        return $this->fail;
    }

    /**
     * @param string $fail
     */
    public function setFail($fail)
    {
        $this->fail = $fail;
    }// (Number of pupil for each measure time)




}