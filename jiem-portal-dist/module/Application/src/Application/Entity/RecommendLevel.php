<?php
/**
 * Dantai Portal (http://dantai.com.jp/)
 *
 * @link      https://fhn-svn.fsoft.com.vn/svn/FSU1.GNC.JIEM-Portal/trunk/Development/SourceCode for the source repository
 * @copyright Copyright (c) 2015 FPT-Software. (http://www.fpt-software.com)
 */
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Crypt\PublicKey\Rsa\PublicKey;

/**
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\RecommendedRepository")
 * @ORM\Table(name="RecommendLevel")
 */
class RecommendLevel extends Common
{

    /* Foreing key */
    /**
     * Foreing key reference to EikenSchedule
     * @ORM\Column(type="integer", name="EikenScheduleId", nullable=true)
     *
     * @var integer
     */
    protected $eikenScheduleId;

    /**
     * Foreing key reference to OrgSchoolYear
     * @ORM\Column(type="integer", name="OrgSchoolYearId", nullable=true)
     *
     * @var integer
     */
    protected $orgSchoolYearId;

    /**
     * Foreing key reference to StandardLevelSetting
     * @ORM\Column(type="integer", name="StandardLevelSettingId", nullable=true)
     *
     * @var integer
     */
    protected $standardLevelSettingId;

    /**
     * 
     * @ORM\Column(type="integer", name="IBATestResultId", nullable=true)
     *
     * @var integer
     */
    protected $iBATestResultId;

    /**
     * 
     * @ORM\Column(type="integer", name="EikenTestResultId", nullable=true)
     *
     * @var integer
     */
    protected $eikenTestResultId;

    /**
     * Foreing key reference to EikenLevel
     * @ORM\Column(type="integer", name="EikenLevelId", nullable=true)
     *
     * @var integer
     */
    protected $eikenLevelId;

    /**
     * Foreing key reference to Pupil
     * @ORM\Column(type="integer", name="PupilId", nullable=true)
     *
     * @var integer
     */
    protected $pupilId;

    /* Property */
    
    /* Relationship */
    /**
     * @ORM\ManyToOne(targetEntity="EikenSchedule")
     * @ORM\JoinColumn(name="EikenScheduleId", referencedColumnName="id")
     */
    protected $eikenSchedule;

    /**
     * @ORM\ManyToOne(targetEntity="OrgSchoolYear")
     * @ORM\JoinColumn(name="OrgSchoolYearId", referencedColumnName="id")
     */
    protected $orgSchoolYear;

    /**
     * @ORM\ManyToOne(targetEntity="StandardLevelSetting")
     * @ORM\JoinColumn(name="StandardLevelSettingId", referencedColumnName="id")
     */
    protected $standardLevelSetting;


    /**
     * @ORM\ManyToOne(targetEntity="EikenLevel")
     * @ORM\JoinColumn(name="EikenLevelId", referencedColumnName="id")
     */
    protected $eikenLevel;

    /**
     * @ORM\ManyToOne(targetEntity="Pupil")
     * @ORM\JoinColumn(name="PupilId", referencedColumnName="id")
     */
    protected $pupil;
    
    /**
     * @ORM\Column(type="boolean", name="IsManuallySet",options={"default":0})
     * @var boolean
     */
    protected $isManuallySet = 0;

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
    public function getStandardLevelSettingId()
    {
        return $this->standardLevelSettingId;
    }

    /**
     *
     * @param int $standardLevelSettingId            
     */
    public function setStandardLevelSettingId($standardLevelSettingId)
    {
        $this->standardLevelSettingId = $standardLevelSettingId;
    }

    /**
     *
     * @return int
     */
    public function getIBATestResultId()
    {
        return $this->iBATestResultId;
    }

    /**
     *
     * @param int $iBATestResultId            
     */
    public function setIBATestResultId($iBATestResultId)
    {
        $this->iBATestResultId = $iBATestResultId;
    }

    /**
     *
     * @return int
     */
    public function getEikenTestResultId()
    {
        return $this->eikenTestResultId;
    }

    /**
     *
     * @param int $eikenTestResultId            
     */
    public function setEikenTestResultId($eikenTestResultId)
    {
        $this->eikenTestResultId = $eikenTestResultId;
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
     * @return string
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
    public function getStandardLevelSetting()
    {
        return $this->standardLevelSetting;
    }

    /**
     *
     * @param mixed $standardLevelSetting            
     */
    public function setStandardLevelSetting($standardLevelSetting)
    {
        $this->standardLevelSetting = $standardLevelSetting;
    }


    /**
     *
     * @return \Application\Entity\EikenLevel
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
    
    /* Getter and Setter */
    function getIsManuallySet() {
        return $this->isManuallySet;
    }

    function setIsManuallySet($isManuallySet) {
        $this->isManuallySet = $isManuallySet;
    }


}