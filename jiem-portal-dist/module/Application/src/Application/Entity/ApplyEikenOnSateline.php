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
 * @ORM\Table(name="ApplyEikenOnSateline")
 */
class ApplyEikenOnSateline extends Common
{

    /* Foreing key */
    /**
     * Foreing key reference to Pupil
     * @ORM\Column(type="integer", name="PupilId", nullable=true)
     *
     * @var integer
     */
    protected $pupilId;

    /**
     * Foreing key reference to City
     * @ORM\Column(type="integer", name="CityId", nullable=true)
     *
     * @var integer
     */
    protected $cityId;

    /**
     * Foreing key reference to OrgSchoolYear
     * @ORM\Column(type="integer", name="OrgSchoolYearId", nullable=true)
     *
     * @var integer
     */
    protected $orgSchoolYearId;

    /**
     * Foreing key reference to EikenSchedule
     * @ORM\Column(type="integer", name="EikenScheduleId", nullable=true)
     *
     * @var integer
     */
    protected $eikenScheduleId;

    /**
     * Foreing key reference to EikenLevel
     * @ORM\Column(type="integer", name="EikenLevelId", nullable=true)
     *
     * @var integer
     */
    protected $eikenLevelId;

    /* Property */
    
    /**
     * @ORM\Column(type="decimal", name="TuitionFee", length=50, nullable=true)
     *
     * @var decimal
     */
    protected $tuitionFee;

    /**
     * @ORM\Column(type="smallint", name="FeeFirstTime", nullable=true)
     */
    protected $feeFirstTime;

    /**
     * @ORM\Column(type="integer", name="FirstPassedTime", nullable=true)
     *
     * @var integer
     */
    protected $firstPassedTime;

    /**
     * @ORM\Column(type="string", name="AreaNumber1", length=250, nullable=true)
     *
     * @var string
     */
    protected $areaNumber1;

    /**
     * @ORM\Column(type="string", name="AreaPersonal1", length=250, nullable=true)
     *
     * @var string
     */
    protected $areaPersonal1;

    /**
     * @ORM\Column(type="string", name="AreaNumber2", length=250, nullable=true)
     *
     * @var string
     */
    protected $areaNumber2;

    /**
     * @ORM\Column(type="string", name="AreaPersonal2", length=250, nullable=true)
     *
     * @var string
     */
    protected $areaPersonal2;

    /**
     * @ORM\Column(type="string", name="AreaNumber3", length=250, nullable=true)
     *
     * @var string
     */
    protected $areaNumber3;

    /**
     * @ORM\Column(type="string", name="AreaPersonal3", length=250, nullable=true)
     *
     * @var string
     */
    protected $areaPersonal3;

    /* Relationship */
    /**
     * @ORM\ManyToOne(targetEntity="Pupil")
     * @ORM\JoinColumn(name="PupilId", referencedColumnName="id")
     */
    protected $pupil;

    /**
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumn(name="CityId", referencedColumnName="id")
     */
    protected $city;

    /**
     * @ORM\ManyToOne(targetEntity="OrgSchoolYear")
     * @ORM\JoinColumn(name="OrgSchoolYearId", referencedColumnName="id")
     */
    protected $orgSchoolYear;

    /**
     * @ORM\ManyToOne(targetEntity="EikenSchedule")
     * @ORM\JoinColumn(name="EikenScheduleId", referencedColumnName="id")
     */
    protected $eikenSchedule;

    /**
     * @ORM\ManyToOne(targetEntity="EikenLevel")
     * @ORM\JoinColumn(name="EikenLevelId", referencedColumnName="id")
     */
    protected $eikenLevel;

    /**
     *
     * @return int
     */
    public function getPupilId()
    {
        return $this->pupilId;
    }

    /**
     *
     * @param int $pupilId            
     */
    public function setPupilId($pupilId)
    {
        $this->pupilId = $pupilId;
    }

    /**
     *
     * @return int
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     *
     * @param int $cityId            
     */
    public function setCityId($cityId)
    {
        $this->cityId = $cityId;
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
    public function getEikenScheduleId()
    {
        return $this->eikenScheduleId;
    }

    /**
     *
     * @param int $eikenScheduleId            
     */
    public function setEikenScheduleId($eikenScheduleId)
    {
        $this->eikenScheduleId = $eikenScheduleId;
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
     * @return mixed
     */
    public function getTuitionFee()
    {
        return $this->tuitionFee;
    }

    /**
     *
     * @param mixed $tuitionFee            
     */
    public function setTuitionFee($tuitionFee)
    {
        $this->tuitionFee = $tuitionFee;
    }

    /**
     *
     * @return mixed
     */
    public function getFeeFirstTime()
    {
        return $this->feeFirstTime;
    }

    /**
     *
     * @param mixed $feeFirstTime            
     */
    public function setFeeFirstTime($feeFirstTime)
    {
        $this->feeFirstTime = $feeFirstTime;
    }

    /**
     *
     * @return mixed
     */
    public function getFirstPassedTime()
    {
        return $this->firstPassedTime;
    }

    /**
     *
     * @param mixed $firstPassedTime            
     */
    public function setFirstPassedTime($firstPassedTime)
    {
        $this->firstPassedTime = $firstPassedTime;
    }

    /**
     *
     * @return mixed
     */
    public function getAreaNumber1()
    {
        return $this->areaNumber1;
    }

    /**
     *
     * @param mixed $areaNumber1            
     */
    public function setAreaNumber1($areaNumber1)
    {
        $this->areaNumber1 = $areaNumber1;
    }

    /**
     *
     * @return string
     */
    public function getAreaPersonal1()
    {
        return $this->areaPersonal1;
    }

    /**
     *
     * @param string $areaPersonal1            
     */
    public function setAreaPersonal1($areaPersonal1)
    {
        $this->areaPersonal1 = $areaPersonal1;
    }

    /**
     *
     * @return mixed
     */
    public function getAreaNumber2()
    {
        return $this->areaNumber2;
    }

    /**
     *
     * @param mixed $areaNumber2            
     */
    public function setAreaNumber2($areaNumber2)
    {
        $this->areaNumber2 = $areaNumber2;
    }

    /**
     *
     * @return string
     */
    public function getAreaPersonal2()
    {
        return $this->areaPersonal2;
    }

    /**
     *
     * @param string $areaPersonal2            
     */
    public function setAreaPersonal2($areaPersonal2)
    {
        $this->areaPersonal2 = $areaPersonal2;
    }

    /**
     *
     * @return string
     */
    public function getAreaNumber3()
    {
        return $this->areaNumber3;
    }

    /**
     *
     * @param string $areaNumber3            
     */
    public function setAreaNumber3($areaNumber3)
    {
        $this->areaNumber3 = $areaNumber3;
    }

    /**
     *
     * @return string
     */
    public function getAreaPersonal3()
    {
        return $this->areaPersonal3;
    }

    /**
     *
     * @param string $areaPersonal3            
     */
    public function setAreaPersonal3($areaPersonal3)
    {
        $this->areaPersonal3 = $areaPersonal3;
    }

    /**
     *
     * @return mixed
     */
    public function getPupil()
    {
        return $this->pupil;
    }

    /**
     *
     * @param mixed $pupil            
     */
    public function setPupil($pupil)
    {
        $this->pupil = $pupil;
    }

    /**
     *
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     *
     * @param mixed $city            
     */
    public function setCity($city)
    {
        $this->city = $city;
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
     * @return mixed
     */
    public function getEikenSchedule()
    {
        return $this->eikenSchedule;
    }

    /**
     *
     * @param mixed $eikenSchedule            
     */
    public function setEikenSchedule($eikenSchedule)
    {
        $this->eikenSchedule = $eikenSchedule;
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
}