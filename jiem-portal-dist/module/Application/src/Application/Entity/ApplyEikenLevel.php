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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\ApplyEikenLevelRepository")
 * @ORM\Table(name="ApplyEikenLevel")
 */
class ApplyEikenLevel extends Common
{

    /* Foreing key */
    /**
     * Foreing key reference to ApplyEikenPersonalInfo
     * @ORM\Column(type="integer", name="ApplyEikenPersonalInfoId", nullable=true)
     *
     * @var integer
     */
    protected $applyEikenPersonalInfoId;

    /**
     * Foreing key reference to ApplyEikenOrgDetails
     * @ORM\Column(type="integer", name="ApplyEikenOrgDetailsId", nullable=true)
     *
     * @var integer
     */
    protected $applyEikenOrgDetailsId;

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
     * @ORM\Column(type="datetime", name="PaymentDate", nullable=true)
     */
    protected $paymentDate;

    /**
     * @ORM\Column(type="smallint", name="PaymentBy", nullable=true)
     */
    protected $paymentBy;
    /**
     * @ORM\Column(type="integer", name="BillId", nullable=true)
     *
     * @var integer
     */
    protected $billId;

    /**
     * @ORM\Column(type="string", name="OrderID", length=50, nullable=true)
     *
     * @var string
     */
    protected $orderId;
    /**
     * @ORM\Column(type="datetime", name="Deadline", nullable=true)
     */
    protected $deadLine;

      /**
     * @ORM\Column(type="string", name="CvsCode", length=10, nullable=true)
     *
     * @var string
     */
    protected $cvsCode;

    /**
     * @ORM\Column(type="boolean", name="IsRegister",options={"default":0})
     *
     * @var boolean
     */
    protected $isRegister;

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
     * @ORM\Column(type="integer", name="CityId1", nullable=true)
     *
     * @var integer
     */
    protected $cityId1;

    /**
     * @ORM\Column(type="integer", name="DistrictId1", nullable=true)
     *
     * @var integer
     */
    protected $districtId1;

    /**
     * @ORM\Column(type="integer", name="CityId2", nullable=true)
     *
     * @var integer
     */
    protected $cityId2;
    /**
     * @ORM\Column(type="integer", name ="PupilId", nullable=true)
     *
     * @var integer
     */
    protected $pupilId;

    /**
     * @ORM\Column(type="integer", name="DistrictId2",nullable=true)
     *
     * @var integer
     */
    protected $districtId2;

    /**
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumn(name="CityId1", referencedColumnName="id")
     */
    protected $areaNumber2;

    /**
     * @ORM\ManyToOne(targetEntity="District")
     * @ORM\JoinColumn(name="DistrictId1", referencedColumnName="id")
     */
    protected $areaPersonal2;

    /**
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumn(name="CityId2", referencedColumnName="id")
     */
    protected $areaNumber3;

    /**
     * @ORM\ManyToOne(targetEntity="District")
     * @ORM\JoinColumn(name="DistrictId2", referencedColumnName="id")
     */
    protected $areaPersonal3;

    /**
     * @ORM\Column(type="datetime", name="RegisterDate", nullable=true)
     */
    protected $registerDate;
    /**
     * @ORM\Column(type="datetime", name="RegDateOnSatellite", nullable=true)
     */
    protected $regDateOnSatellite;
    /**
     * @ORM\Column(type="boolean", name="IsSateline", options={"default":1})
     *
     * @var boolean
     */
    protected $isSateline;
    /**
     * @ORM\Column(type="boolean", name="PaymentStatus", nullable=true,options={"comment":"0:Chưa thanh toán; 1:Đã thanh toán"})
     *
     * @var boolean
     */
    protected $paymentStatus;
    /**
     * @ORM\Column(type="boolean", name="RegisterStatus", nullable=true,options={"comment":"0:Chưa đăng ký; 1:Đã đăng ký"})
     *
     * @var boolean
     */
    protected $registerStatus;
    /**
     * @ORM\Column(type="boolean", name="IsSubmit", nullable=true,options={"comment":"0:Chưa submit; 1:Đã submit"})
     *
     * @var boolean
     */
    protected $isSubmit;
    /**
     * @ORM\Column(type="boolean", name="IsCancel", nullable=true,options={"comment":"0:Chưa cancel; 1:Đã cancel"})
     *
     * @var boolean
     */
    protected $isCancel;
    /* Relationship */
    /**
     * Reference to table ApplyEikenPersonalInfo
     * @ORM\ManyToOne(targetEntity="ApplyEikenPersonalInfo")
     * @ORM\JoinColumn(name="ApplyEikenPersonalInfoId", referencedColumnName="id")
     */
    protected $applyEikenPersonalInfo;

    /**
     * Reference to table City
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumn(name="CityId", referencedColumnName="id")
     */
    protected $city;

    /**
     * Reference to table OrgSchoolYear
     * @ORM\ManyToOne(targetEntity="OrgSchoolYear")
     * @ORM\JoinColumn(name="OrgSchoolYearId", referencedColumnName="id")
     */
    protected $orgSchoolYear;

    /**
     * Reference to table EikenSchedule
     * @ORM\ManyToOne(targetEntity="EikenSchedule")
     * @ORM\JoinColumn(name="EikenScheduleId", referencedColumnName="id")
     */
    protected $eikenSchedule;
     /**
     * @ORM\Column(type="string", name="FirstPassedTimeFree", length=250, nullable=true)
     *
     * @var string
     */
    protected $firstPassedTime;

    /**
     * Reference to table ApplyEikenOrgDetails
     * @ORM\ManyToOne(targetEntity="ApplyEikenOrgDetails")
     * @ORM\JoinColumn(name="ApplyEikenOrgDetailsId", referencedColumnName="id")
     */
    protected $applyEikenOrgDetail;

    /**
     * Reference to table EikenLevel
     * @ORM\ManyToOne(targetEntity="EikenLevel")
     * @ORM\JoinColumn(name="EikenLevelId", referencedColumnName="id")
     */
    protected $eikenLevel;
    /**
     * @ORM\ManyToOne(targetEntity="Pupil")
     * @ORM\JoinColumn(name="PupilId", referencedColumnName="id")
     */
    protected $pupil;

    protected $encryptId;
    
    /**
     * @ORM\Column(type="string", name="OldEikenID", length=50, nullable=true)
     *
     * @var string
     */
    protected $oldEikenId;
    
     /**
     * @ORM\Column(type="integer", name="HallType", nullable=true)
     *
     * @var integer
     */
    protected $hallType;
    
    /**
     * @ORM\Column(type="integer", name="BlockCombini", nullable=true)
     *
     * @var integer
     */
    protected $blockCombini;
    
    /**
     * @ORM\Column(type="integer", name="IsDiscount", nullable=true)
     *
     * @var integer
     */
    protected $isDiscount;

    /* Getter and Setter */
    /**
     *
     * @return int
     */
    public function getApplyEikenPersonalInfoId()
    {
        return $this->applyEikenPersonalInfoId;
    }

    /**
     *
     * @param int $applyEikenPersonalInfoId
     */
    public function setApplyEikenPersonalInfoId($applyEikenPersonalInfoId)
    {
        $this->applyEikenPersonalInfoId = $applyEikenPersonalInfoId;
    }

    /**
     *
     * @return int
     */
    public function getApplyEikenOrgDetailsId()
    {
        return $this->applyEikenOrgDetailsId;
    }

    /**
     *
     * @param int $applyEikenOrgDetailsId
     */
    public function setApplyEikenOrgDetailsId($applyEikenOrgDetailsId)
    {
        $this->applyEikenOrgDetailsId = $applyEikenOrgDetailsId;
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
    public function getPaymentDate()
    {
        return $this->paymentDate;
    }

    /**
     *
     * @param mixed $paymentDate
     */
    public function setPaymentDate($paymentDate)
    {
        $this->paymentDate = $paymentDate;
    }

    /**
     *
     * @return mixed
     */
    public function getPaymentBy()
    {
        return $this->paymentBy;
    }

    /**
     *
     * @param mixed $paymentBy
     */
    public function setPaymentBy($paymentBy)
    {
        $this->paymentBy = $paymentBy;
    }

    /**
     *
     * @return mixed
     */
    public function getIsRegister()
    {
        return $this->isRegister;
    }

    /**
     *
     * @param mixed $isRegister
     */
    public function setIsRegister($isRegister)
    {
        $this->isRegister = $isRegister;
    }

    /**
     * @return boolean
     */
    public function getPaymentStatus()
    {
        return $this->paymentStatus;
    }

    /**
     * @param boolean $paymentStatus
     */
    public function setPaymentStatus($paymentStatus)
    {
        $this->paymentStatus = $paymentStatus;
    }

    /**
     * @return boolean
     */
    public function getRegisterStatus()
    {
        return $this->registerStatus;
    }

    /**
     * @param boolean $registerStatus
     */
    public function setRegisterStatus($registerStatus)
    {
        $this->registerStatus = $registerStatus;
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
     * @return string
     */
    public function getFirstPassedTime()
    {
        return $this->firstPassedTime;
    }

    /**
     *
     * @param string $firstPassedTime
     */
    public function setFirstPassedTime($firstPassedTime)
    {
        $this->firstPassedTime = $firstPassedTime;
    }

    /**
     *
     * @return string
     */
    public function getAreaNumber1()
    {
        return $this->areaNumber1;
    }

    /**
     *
     * @param string $areaNumber1
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
    public function getRegisterDate()
    {
        return $this->registerDate;
    }

    /**
     *
     * @param mixed $registerDate
     */
    public function setRegisterDate($registerDate)
    {
        $this->registerDate = $registerDate;
    }

    /**
     *
     * @return mixed
     */
    public function getApplyEikenPersonalInfo()
    {
        return $this->applyEikenPersonalInfo;
    }

    /**
     *
     * @param mixed $applyEikenPersonalInfo
     */
    public function setApplyEikenPersonalInfo($applyEikenPersonalInfo)
    {
        $this->applyEikenPersonalInfo = $applyEikenPersonalInfo;
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
    public function getApplyEikenOrgDetail()
    {
        return $this->applyEikenOrgDetail;
    }

    /**
     *
     * @param mixed $applyEikenOrgDetail
     */
    public function setApplyEikenOrgDetail($applyEikenOrgDetail)
    {
        $this->applyEikenOrgDetail = $applyEikenOrgDetail;
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
     * @return int
     */
    public function getCityId1()
    {
        return $this->cityId1;
    }

    /**
     *
     * @param int $cityId1
     */
    public function setCityId1($cityId1)
    {
        $this->cityId1 = $cityId1;
    }

    /**
     *
     * @return mixed
     */
    public function getAreaPersonal3()
    {
        return $this->areaPersonal3;
    }

    /**
     *
     * @param mixed $areaPersonal3
     */
    public function setAreaPersonal3($areaPersonal3)
    {
        $this->areaPersonal3 = $areaPersonal3;
    }

    /**
     *
     * @return mixed
     */
    public function getAreaNumber3()
    {
        return $this->areaNumber3;
    }

    /**
     *
     * @param mixed $areaNumber3
     */
    public function setAreaNumber3($areaNumber3)
    {
        $this->areaNumber3 = $areaNumber3;
    }

    /**
     *
     * @return mixed
     */
    public function getAreaPersonal2()
    {
        return $this->areaPersonal2;
    }

    /**
     *
     * @param mixed $areaPersonal2
     */
    public function setAreaPersonal2($areaPersonal2)
    {
        $this->areaPersonal2 = $areaPersonal2;
    }

    /**
     *
     * @return int
     */
    public function getDistrictId2()
    {
        return $this->districtId2;
    }

    /**
     *
     * @param int $districtId2
     */
    public function setDistrictId2($districtId2)
    {
        $this->districtId2 = $districtId2;
    }

    /**
     *
     * @return int
     */
    public function getCityId2()
    {
        return $this->cityId2;
    }

    /**
     *
     * @param int $cityId2
     */
    public function setCityId2($cityId2)
    {
        $this->cityId2 = $cityId2;
    }

    /**
     *
     * @return int
     */
    public function getDistrictId1()
    {
        return $this->districtId1;
    }

    /**
     *
     * @param int $districtId1
     */
    public function setDistrictId1($districtId1)
    {
        $this->districtId1 = $districtId1;
    }

    /**
     * @return mixed
     */
    public function getRegDateOnSatellite()
    {
        return $this->regDateOnSatellite;
    }

    /**
     * @param mixed $regDateOnSatellite
     */
    public function setRegDateOnSatellite($regDateOnSatellite)
    {
        $this->regDateOnSatellite = $regDateOnSatellite;
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
     * @return int
     */
    public function getPupilId()
    {
        return $this->pupilId;
    }

    /**
     * @param int $pupilId
     */
    public function setPupilId($pupilId)
    {
        $this->pupilId = $pupilId;
    }

    /**
     * @return mixed
     */
    public function getPupil()
    {
        return $this->pupil;
    }

    /**
     * @param mixed $pupil
     */
    public function setPupil($pupil)
    {
        $this->pupil = $pupil;
    }

    /**
     * @return mixed
     */
    public function getIsSateline()
    {
        return $this->isSateline;
    }

    /**
     * @param mixed $isSateline
     */
    public function setIsSateline($isSateline)
    {
        $this->isSateline = $isSateline;
    }

    /**
     * @return int
     */
    public function getBillId()
    {
        return $this->billId;
    }

    /**
     * @param int $billId
     */
    public function setBillId($billId)
    {
        $this->billId = $billId;
    }

    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @return mixed
     */
    public function getDeadLine()
    {
        return $this->deadLine;
    }

    /**
     * @param mixed $deadLine
     */
    public function setDeadLine($deadLine)
    {
        $this->deadLine = $deadLine;
    }

    /**
     * @return mixed
     */
    public function getCvsCode()
    {
        return $this->cvsCode;
    }

    /**
     * @param mixed $cvsCode
     */
    public function setCvsCode($cvsCode)
    {
        $this->cvsCode = $cvsCode;
    }

    /**
     * @return boolean
     */
    public function isIsSubmit()
    {
        return $this->isSubmit;
    }

    /**
     * @param boolean $isSubmit
     */
    public function setIsSubmit($isSubmit)
    {
        $this->isSubmit = $isSubmit;
    }

    /**
     * @return boolean
     */
    public function isIsCancel()
    {
        return $this->isCancel;
    }

    /**
     * @param boolean $isCancel
     */
    public function setIsCancel($isCancel)
    {
        $this->isCancel = $isCancel;
    }

    public function setEncryptId ($encryptId)
    {
        $this->encryptId = $encryptId;
    }
    public function getEncryptId ()
    {
        return $this->encryptId;
    }
    /**
     *
     * @return string
     */
    public function getOldEikenId()
    {
        return $this->oldEikenId;
    }
    
    /**
     *
     * @param string $oldEikenId
     */
    public function setOldEikenId($oldEikenId)
    {
        $this->oldEikenId = $oldEikenId;
    }
    /**
     * @return int
     */
    public function getHallType()
    {
        return $this->hallType;
    }

    /**
     * @param int $hallType
     */
    public function setHallType($hallType)
    {
        $this->hallType = $hallType;
    }
    
    /**
     * @return int
     */
    public function getBlockCombini()
    {
        return $this->blockCombini;
    }

    /**
     * @param int $blockCombini
     */
    public function setBlockCombini($blockCombini)
    {
        $this->blockCombini = $blockCombini;
    }
    
        /**
     * @return int
     */
    public function getIsDiscount()
    {
        return $this->blockCombini;
    }

    /**
     * @param int $isDiscount
     */
    public function setIsDiscount($isDiscount)
    {
        $this->isDiscount = $isDiscount;
    }
}