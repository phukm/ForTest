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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\ActualExamResultRepository")
 * @ORM\Table(name="ActualExamResult")
 */
class ActualExamResult extends Common
{

    /* Foreing key */
    /**
     * Foreing key reference to OrganizationClassification
     * @ORM\Column(type="integer", name="OrgClassificationId",nullable=true) *
     *
     * @var integer
     */
    protected $orgClassificationId;

    /**
     * Foreing key reference to Organization
     * @ORM\Column(type="integer", name="OrganizationId", nullable=true)
     *
     * @var integer
     */
    protected $organizationId;

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

    /* Relationship */
    /**
     * Reference to table OrganizationClassification
     * @ORM\ManyToOne(targetEntity="OrganizationClassification")
     * @ORM\JoinColumn(name="OrgClassificationId", referencedColumnName="id")
     */
    protected $organizationClassification;

    /**
     * Reference to table Organization
     * @ORM\ManyToOne(targetEntity="Organization")
     * @ORM\JoinColumn(name="OrganizationId", referencedColumnName="id")
     */
    protected $organization;

    /**
     * Reference to table EikenLevel
     * @ORM\ManyToOne(targetEntity="EikenLevel")
     * @ORM\JoinColumn(name="EikenLevelId", referencedColumnName="id")
     */
    protected $eikenLevel;

    /**
     * Reference to table OrgSchoolYear
     * @ORM\ManyToOne(targetEntity="OrgSchoolYear")
     * @ORM\JoinColumn(name="OrgSchoolYearId", referencedColumnName="id")
     */
    protected $orgSchoolYear;

    /* Property */
    
    /**
     * @ORM\Column(type="integer", name="Year", length=4, nullable=true)
     *
     * @var integer
     */
    protected $year;

    /**
     * @ORM\Column(type="integer", name="Time", length=2, nullable=true)
     *
     * @var integer
     */
    protected $time;

    /**
     * @ORM\Column(type="string", name="StudentCode", length=11, nullable=true)
     *
     * @var string
     */
    protected $studentCode;

    /**
     * @ORM\Column(type="string", name="StudentName", nullable=true)
     *
     * @var string
     */
    protected $studentName;

    /**
     * @ORM\Column(type="string", name="Kumi", nullable=true)
     *
     * @var string
     */
    protected $kumi;

    /**
     * @ORM\Column(type="integer", name="PassFlag", nullable=true)
     *
     * @var integer
     */
    protected $passFlag;

    /**
     * @ORM\Column(type="string", name="FailType1", nullable=true)
     *
     * @var boolean
     */
    protected $failType1;

    /**
     * @ORM\Column(type="string", name="FailType2", nullable=true)
     *
     * @var string
     */
    protected $failType2;

    /**
     * @ORM\Column(type="boolean", name="PassFail1", nullable=true)
     *
     * @var string
     */
    protected $passFail1;

    /**
     * @ORM\Column(type="boolean", name="PassFail2", nullable=true)
     *
     * @var boolean
     */
    protected $passFail2;

    /**
     * @ORM\Column(type="boolean", name="IsExemption", nullable=true)
     *
     * @var boolean
     */
    protected $isExemption;

    /**
     * @ORM\Column(type="integer", name="AttendFlag", nullable=true)
     *
     * @var integer
     */
    protected $attendFlag;

    /**
     *
     * @return int
     */
    public function getOrgClassificationId()
    {
        return $this->orgClassificationId;
    }

    /**
     *
     * @param int $orgClassificationId            
     */
    public function setOrgClassificationId($orgClassificationId)
    {
        $this->orgClassificationId = $orgClassificationId;
    }

    /**
     *
     * @return int
     */
    public function getOrganizationId()
    {
        return $this->organizationId;
    }

    /**
     *
     * @param int $organizationId            
     */
    public function setOrganizationId($organizationId)
    {
        $this->organizationId = $organizationId;
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
     * @return mixed
     */
    public function getOrganizationClassification()
    {
        return $this->organizationClassification;
    }

    /**
     *
     * @param mixed $organizationClassification            
     */
    public function setOrganizationClassification($organizationClassification)
    {
        $this->organizationClassification = $organizationClassification;
    }

    /**
     *
     * @return mixed
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     *
     * @param mixed $organization            
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
    }

    /**
     *
     * @return mixed
     */
    public function getEikenLevel()
    {
        return $this->eikenLevel;
    }

    /**
     *
     * @param mixed $eikenLevel            
     */
    public function setEikenLevel($eikenLevel)
    {
        $this->eikenLevel = $eikenLevel;
    }

    /**
     *
     * @return mixed
     */
    public function getOrgSchoolYear()
    {
        return $this->orgSchoolYear;
    }

    /**
     *
     * @param mixed $orgSchoolYear            
     */
    public function setOrgSchoolYear($orgSchoolYear)
    {
        $this->orgSchoolYear = $orgSchoolYear;
    }

    /**
     *
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     *
     * @param int $year            
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     *
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     *
     * @param int $time            
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     *
     * @return string
     */
    public function getStudentCode()
    {
        return $this->studentCode;
    }

    /**
     *
     * @param string $studentCode            
     */
    public function setStudentCode($studentCode)
    {
        $this->studentCode = $studentCode;
    }

    /**
     *
     * @return string
     */
    public function getStudentName()
    {
        return $this->studentName;
    }

    /**
     *
     * @param string $studentName            
     */
    public function setStudentName($studentName)
    {
        $this->studentName = $studentName;
    }

    /**
     *
     * @return string
     */
    public function getKumi()
    {
        return $this->kumi;
    }

    /**
     *
     * @param string $kumi            
     */
    public function setKumi($kumi)
    {
        $this->kumi = $kumi;
    }

    /**
     *
     * @return int
     */
    public function getPassFlag()
    {
        return $this->passFlag;
    }

    /**
     *
     * @param int $passFlag            
     */
    public function setPassFlag($passFlag)
    {
        $this->passFlag = $passFlag;
    }

    /**
     *
     * @return boolean
     */
    public function isFailType1()
    {
        return $this->failType1;
    }

    /**
     *
     * @param boolean $failType1            
     */
    public function setFailType1($failType1)
    {
        $this->failType1 = $failType1;
    }

    /**
     *
     * @return boolean
     */
    public function isFailType2()
    {
        return $this->failType2;
    }

    /**
     *
     * @param boolean $failType2            
     */
    public function setFailType2($failType2)
    {
        $this->failType2 = $failType2;
    }

    /**
     *
     * @return boolean
     */
    public function isPassFail1()
    {
        return $this->passFail1;
    }

    /**
     *
     * @param boolean $passFail1            
     */
    public function setPassFail1($passFail1)
    {
        $this->passFail1 = $passFail1;
    }

    /**
     *
     * @return boolean
     */
    public function isPassFail2()
    {
        return $this->passFail2;
    }

    /**
     *
     * @param boolean $passFail2            
     */
    public function setPassFail2($passFail2)
    {
        $this->passFail2 = $passFail2;
    }

    /**
     *
     * @return boolean
     */
    public function isIsExemption()
    {
        return $this->isExemption;
    }

    /**
     *
     * @param boolean $isExemption            
     */
    public function setIsExemption($isExemption)
    {
        $this->isExemption = $isExemption;
    }

    /**
     *
     * @return int
     */
    public function getAttendFlag()
    {
        return $this->attendFlag;
    }

    /**
     *
     * @param int $attendFlag            
     */
    public function setAttendFlag($attendFlag)
    {
        $this->attendFlag = $attendFlag;
    }
}