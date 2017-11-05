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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\ApplyEikenOrgRepository")
 * @ORM\Table(name="ApplyEikenOrg")
 */
class ApplyEikenOrg extends Common
{

    /* Foreing key */
    /**
     * Foreing key reference to Organization
     * @ORM\Column(type="integer", name="OrganizationId", nullable=true)
     *
     * @var integer
     */
    protected $organizationId;

    /**
     * Foreing key reference to EikenSchedule
     * @ORM\Column(type="integer", name="EikenScheduleId", nullable=true)
     *
     * @var integer
     */
    protected $eikenScheduleId;
    /**
     * Foreing key reference to City
     * @ORM\Column(type="integer", name="CityId", nullable=true)
     *
     * @var integer
     */
    protected $cityId;
    /**
     * Foreing key reference to District
     * @ORM\Column(type="integer", name="DistrictId", nullable=true)
     *
     * @var integer
     */
    protected $districtId;

    /* Property */

    /**
     * @ORM\Column(type="integer", name="Total", nullable=true)
     *
     * @var integer
     */
    protected $total;

    /**
     * @ORM\Column(type="integer", name="TypeExamDate", nullable=true)
     *
     * @var integer
     */
    protected $typeExamDate;

    /**
     *
     * @ORM\Column(type="integer", name="ActualExamDate", nullable=true)
     *
     * @var integer
     */
    protected $actualExamDate;

    /**
     * @ORM\Column(type="string", name="CD", length=20, nullable=true)
     *
     * @var string
     */
    protected $cd;

    /**
     * @ORM\Column(type="string", name="FirtNameKanji", length=50, nullable=true)
     *
     * @var string
     */
    protected $firtNameKanji;

    /**
     * @ORM\Column(type="string", name="LastNameKanji", length=50, nullable=true)
     *
     * @var string
     */
    protected $lastNameKanji;

    /**
     * @ORM\Column(type="string", name="MailAddress", length=100, nullable=true)
     *
     * @var string
     */
    protected $mailAddress;
    /**
     * @ORM\Column(type="string", name="ConfirmEmail", length=100, nullable=true)
     *
     * @var string
     */
    protected $confirmEmail;

    /**
     * @ORM\Column(type="string", name="PhoneNumber", length=20, nullable=true)
     *
     * @var string
     */
    protected $phoneNumber;

    /**
     * @ORM\Column(type="smallint", name="LocationType", nullable=true)
     */
    protected $locationType;

    /**
     * @ORM\Column(type="smallint", name="LocationType1", nullable=true)
     */
    protected $locationType1;

    /**
     * @ORM\Column(type="string", name="EikenOrgNo1", length=50, nullable=true)
     *
     * @var string
     */
    protected $eikenOrgNo1;

    /**
     * @ORM\Column(type="string", name="EikenOrgNo2", length=50, nullable=true)
     *
     * @var string
     */
    protected $eikenOrgNo2;
    /**
     * @ORM\Column(type="string", name="EikenOrgNo123", length=50, nullable=true)
     *
     * @var string
     */
    protected $eikenOrgNo123;

    /**
     * @ORM\Column(type="string", name="ApplyStatus", length=50, nullable=true)
     *
     * @var string
     */
    protected $applyStatus;

    /**
     * @ORM\Column(type="string", name="Status", length=50, nullable=true)
     *
     * @var string
     */
    protected $status;
    /**
     * @ORM\Column(type="integer", name="NoApiCalls",nullable=true)
     *
     * @var integer
     */
    protected $noApiCalls;

    /* Relationship */
    /**
     * @ORM\ManyToOne(targetEntity="Organization")
     * @ORM\JoinColumn(name="OrganizationId", referencedColumnName="id")
     */
    protected $organization;

    /**
     * @ORM\ManyToOne(targetEntity="EikenSchedule")
     * @ORM\JoinColumn(name="EikenScheduleId", referencedColumnName="id")
     */
    protected $eikenSchedule;

    /**
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumn(name="CityId", referencedColumnName="id")
     */
    protected $city;

    /**
     * @ORM\ManyToOne(targetEntity="District")
     * @ORM\JoinColumn(name="DistrictId", referencedColumnName="id")
     */
    protected $district;
    /**
     * @ORM\Column(type="boolean", name="HasMainHall", nullable=true)
     *  @var boolean
     */
    protected $hasMainHall;


    /**
     * @ORM\Column(type="smallint", name="isSentStandardHall", nullable=true)
     */
    protected $isSentStandardHall;

    /**
     * @ORM\Column(type="smallint", name="isSentMainHall", nullable=true)
     */
    protected $isSentMainHall;
    /**
     * @ORM\Column(type="string", name="ManagerName", length=50, nullable=true)
     *
     * @var string
     */
    protected $managerName;

    /**
     * @ORM\Column(type="smallint", name="StatusMapping",  nullable=true)
     *  @var boolean
     */
    protected $statusMapping;
    /**
     * @ORM\Column(type="smallint", name="StatusImporting", nullable=true)
     *  @var boolean
     */
    protected $statusImporting;
    /**
     * @ORM\Column(type="integer", name="TotalImport",  nullable=true)
     *  @var integer
     */
    protected $totalImport;
    
    /**
     * @ORM\Column(type="datetime", name="RegistrationDate", nullable=true)
     *
     * @var datetime
     */
    protected $registrationDate;
    
    /**
     * @ORM\Column(type="integer", name="StatusAutoImport",  nullable=true)
     *  @var integer
     */
    protected $statusAutoImport = 0;
    
    /**
     * @ORM\Column(type="string", name="Session",length=32, nullable=true)
     *
     * @var string
     */
    protected $session;
    
    /**
     * @ORM\Column(type="integer", name="StatusRefund",  nullable=true)
     *  @var integer
     */
    protected $statusRefund = 0;

    /**
     * @ORM\Column(type="string", name="ExecutorName",  nullable=true)
     *  @var string
     */
    protected $executorName;

    /**
     * @ORM\Column(type="datetime", name="ConfirmationDate", nullable=true)
     *
     * @var datetime
     */
    protected $confirmationDate;
    
    /**
     * @return datetime
     */
    public function getRegistrationDate(){
        return  $this->registrationDate;
    }
    
    /**
     * Setter for RegistrationDate
     * @param string $registrationDate
     */
    public function setRegistrationDate($registrationDate){
        $this->registrationDate = $registrationDate;
    }

    /**
     * @return datetime
     */
    public function getConfirmationDate(){
        return  $this->confirmationDate;
    }

    /**
     * Setter for ConfirmationDate
     * @param string $confirmationDate
     */
    public function setConfirmationDate($confirmationDate){
        $this->confirmationDate = $confirmationDate;
    }

    /**
     * @return int
     */
    public function getNoApiCalls()
    {
        return $this->noApiCalls;
    }

    /**
     * @param int $noApiCalls
     */
    public function setNoApiCalls($noApiCalls)
    {
        $this->noApiCalls = $noApiCalls;
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
    public function getEikenScheduleId()
    {
        return $this->eikenScheduleId;
    }

    /**
     * @param int $eikenScheduleId
     */
    public function setEikenScheduleId($eikenScheduleId)
    {
        $this->eikenScheduleId = $eikenScheduleId;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param int $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return int
     */
    public function getTypeExamDate()
    {
        return $this->typeExamDate;
    }

    /**
     * @param int $typeExamDate
     */
    public function setTypeExamDate($typeExamDate)
    {
        $this->typeExamDate = $typeExamDate;
    }

    /**
     * @return string
     */
    public function getCd()
    {
        return $this->cd;
    }

    /**
     * @param string $cd
     */
    public function setCd($cd)
    {
        $this->cd = $cd;
    }

    /**
     * @return string
     */
    public function getFirtNameKanji()
    {
        return $this->firtNameKanji;
    }

    /**
     * @param string $firtNameKanji
     */
    public function setFirtNameKanji($firtNameKanji)
    {
        $this->firtNameKanji = $firtNameKanji;
    }

    /**
     * @return string
     */
    public function getLastNameKanji()
    {
        return $this->lastNameKanji;
    }

    /**
     * @param string $lastNameKanji
     */
    public function setLastNameKanji($lastNameKanji)
    {
        $this->lastNameKanji = $lastNameKanji;
    }

    /**
     * @return string
     */
    public function getMailAddress()
    {
        return $this->mailAddress;
    }

    /**
     * @param string $mailAddress
     */
    public function setMailAddress($mailAddress)
    {
        $this->mailAddress = $mailAddress;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return mixed
     */
    public function getLocationType()
    {
        return $this->locationType;
    }

    /**
     * @param mixed $locationType
     */
    public function setLocationType($locationType)
    {
        $this->locationType = $locationType;
    }

    /**
     * @return mixed
     */
    public function getLocationType1()
    {
        return $this->locationType1;
    }

    /**
     * @param mixed $locationType1
     */
    public function setLocationType1($locationType1)
    {
        $this->locationType1 = $locationType1;
    }

    /**
     * @return string
     */
    public function getEikenOrgNo1()
    {
        return $this->eikenOrgNo1;
    }

    /**
     * @param string $eikenOrgNo1
     */
    public function setEikenOrgNo1($eikenOrgNo1)
    {
        $this->eikenOrgNo1 = $eikenOrgNo1;
    }

    /**
     * @return string
     */
    public function getEikenOrgNo2()
    {
        return $this->eikenOrgNo2;
    }

    /**
     * @param string $eikenOrgNo2
     */
    public function setEikenOrgNo2($eikenOrgNo2)
    {
        $this->eikenOrgNo2 = $eikenOrgNo2;
    }

    /**
     * @return string
     */
    public function getApplyStatus()
    {
        return $this->applyStatus;
    }

    /**
     * @param string $applyStatus
     */
    public function setApplyStatus($applyStatus)
    {
        $this->applyStatus = $applyStatus;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param mixed $organization
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
    }

    /**
     * @return mixed
     */
    public function getEikenSchedule()
    {
        return $this->eikenSchedule;
    }

    /**
     * @param mixed $eikenSchedule
     */
    public function setEikenSchedule($eikenSchedule)
    {
        $this->eikenSchedule = $eikenSchedule;
    }

    /**
     * @return string
     */
    public function getEikenOrgNo123()
    {
        return $this->eikenOrgNo123;
    }

    /**
     * @param string $eikenOrgNo123
     */
    public function setEikenOrgNo123($eikenOrgNo123)
    {
        $this->eikenOrgNo123 = $eikenOrgNo123;
    }

    /**
     * @return mixed
     */
    public function getHasMainHall()
    {
        return $this->hasMainHall;
    }

    /**
     * @param mixed $hasMainHall
     */
    public function setHasMainHall($hasMainHall)
    {
        $this->hasMainHall = $hasMainHall;
    }

    /**
     * @return int
     */
    public function getActualExamDate()
    {
        return $this->actualExamDate;
    }

    /**
     * @param int $actualExamDate
     */
    public function setActualExamDate($actualExamDate)
    {
        $this->actualExamDate = $actualExamDate;
    }

    /**
     * @return int
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     * @param int $cityId
     */
    public function setCityId($cityId)
    {
        $this->cityId = $cityId;
    }

    /**
     * @return int
     */
    public function getDistrictId()
    {
        return $this->districtId;
    }

    /**
     * @param int $districtId
     */
    public function setDistrictId($districtId)
    {
        $this->districtId = $districtId;
    }

    /**
     * @return string
     */
    public function getConfirmEmail()
    {
        return $this->confirmEmail;
    }

    /**
     * @param string $confirmEmail
     */
    public function setConfirmEmail($confirmEmail)
    {
        $this->confirmEmail = $confirmEmail;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * @param mixed $district
     */
    public function setDistrict($district)
    {
        $this->district = $district;
    }

    /**
     * @param mixed $isSentStandardHall
     */
    public function setIsSentStandardHall($isSentStandardHall)
    {
        $this->isSentStandardHall = $isSentStandardHall;
    }

    /**
     * @return mixed
     */
    public function getIsSentStandardHall()
    {
        return $this->isSentStandardHall;
    }

    /**
     * @param mixed $isSentMainHall
     */
    public function setIsSentMainHall($isSentMainHall)
    {
        $this->isSentMainHall = $isSentMainHall;
    }

    /**
     * @return mixed
     */
    public function getIsSentMainHall()
    {
        return $this->isSentMainHall;
    }

    /**
     * @return boolean
     */
    public function getStatusMapping()
    {
        return $this->statusMapping;
    }

    /**
     * @param boolean $statusMapping
     */
    public function setStatusMapping($statusMapping)
    {
        $this->statusMapping = $statusMapping;
    }

    /**
     * @return boolean
     */
    public function getStatusImporting()
    {
        return $this->statusImporting;
    }

    /**
     * @param boolean $statusImporting
     */
    public function setStatusImporting($statusImporting)
    {
        $this->statusImporting = $statusImporting;
    }

    /**
     * @return int
     */
    public function getTotalImport()
    {
        return $this->totalImport;
    }

    /**
     * @param int $totalImport
     */
    public function setTotalImport($totalImport)
    {
        $this->totalImport = $totalImport;
    }

    /**
     * @return string
     */
    public function getManagerName()
    {
        return $this->managerName;
    }

    /**
     * @param string $managerName
     */
    public function setManagerName($managerName)
    {
        $this->managerName = $managerName;
    }
    
    /**
     * @return int
     */
    public function getStatusAutoImport()
    {
        return $this->statusAutoImport;
    }

    /**
     * @param int $statusAutoImport
     */
    public function setStatusAutoImport($statusAutoImport)
    {
        $this->statusAutoImport = $statusAutoImport;
    }
    
   
    /**
     *
     * @return string
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     *
     * @param string $session
     */
    public function setSession($session)
    {
        $this->session = $session;
    }
    
    /**
     * @return int
     */
    public function getStatusRefund()
    {
        return $this->statusRefund;
    }

    /**
     * @param int $statusRefund
     */
    public function setStatusRefund($statusRefund)
    {
        $this->statusRefund = $statusRefund;
    }

    /**
     * @param string $executorName
     */
    public function setExecutorName($executorName)
    {
        $this->executorName = $executorName;
    }

    /**
     * @return string
     */
    public function getExecutorName()
    {
        return $this->executorName;
    }
}