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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\OrganizationRepository")
 * @ORM\Table(name="Organization")
 */
class Organization extends Common
{

    /* Foreing key */
    /**
     * @ORM\Column(type="integer", name="CityId", nullable=true)
     *
     * @var integer
     */
    protected $cityId;
    /* Property */
    /**
     * @ORM\Column(type="string", name="OrganizationNo", length=50, nullable=true)
     *
     * @var string
     */
    protected $organizationNo;

    /**
     * @ORM\Column(type="string", name="OrganizationCode", length=50, nullable=true)
     *
     * @var string
     */
    protected $organizationCode;

    /**
     * @ORM\Column(type="string", name="OrgNameKanji", length=250, nullable=true)
     *
     * @var string
     */
    protected $orgNameKanji;

    /**
     * @ORM\Column(type="string", name="OrgNameKana", length=250, nullable=true)
     *
     * @var string
     */
    protected $orgNameKana;

    /**
     * @ORM\Column(type="string", name="Department", length=100, nullable=true)
     *
     * @var string
     */
    protected $department;
    /**
     * @ORM\Column(type="string", name="ReceptionTime", length=500, nullable=true)
     *
     * @var string
     */
    protected $receptionTime;

    /**
     * @ORM\Column(type="integer", name="SchoolDivision", nullable=true)
     *
     * @var string
     */
    protected $schoolDivision;

    /**
     * @ORM\Column(type="integer", name="FlagRegister", nullable=true)
     *
     * @var integer
     */
    protected $flagRegister;

    /**
     * @ORM\Column(type="string", name="`Group`", length=250, nullable=true)
     *
     * @var string
     */
    protected $group;

    /**
     * @ORM\Column(type="string", name="ExamLocation", length=250, nullable=true)
     *
     * @var string
     */
    protected $examLocation;

    /**
     * @ORM\Column(type="string", name="ExamLand", length=250, nullable=true)
     *
     * @var string
     */
    protected $examLand;

    /**
     * @ORM\Column(type="string", name="OfficerName", length=250, nullable=true)
     *
     * @var string
     */
    protected $officerName;

    /**
     * @ORM\Column(type="string", name="CityCode", length=50, nullable=true)
     *
     * @var string
     */
    protected $cityCode;



    /**
     * @ORM\Column(type="string", name="Address1", length=500, nullable=true)
     *
     * @var string
     */
    protected $address1;

    /**
     * @ORM\Column(type="string", name="StateCode", length=50, nullable=true)
     *
     * @var string
     */
    protected $stateCode;

    /**
     * @ORM\Column(type="string", name="TownCode", length=50, nullable=true)
     *
     * @var string
     */
    protected $townCode;

    /**
     * @ORM\Column(type="string", name="Address2", length=500, nullable=true)
     *
     * @var string
     */
    protected $address2;

    /**
     * @ORM\Column(type="string", name="Email", length=100, nullable=true)
     *
     * @var string
     */
    protected $email;

    /**
     * @ORM\Column(type="string", name="TelNo", length=50, nullable=true)
     *
     * @var string
     */
    protected $telNo;

    /**
     * @ORM\Column(type="string", name="Fax", length=50, nullable=true)
     *
     * @var string
     */
    protected $fax;

    /**
     * @ORM\Column(type="string", name="Passcode", length=50, nullable=true)
     *
     * @var string
     */
    protected $passcode;
    /**
     * @ORM\ManyToOne(targetEntity="City")
     *@ORM\JoinColumn(name="CityId", referencedColumnName="id")
     * @var integer
     */
    protected $city;

    /**
     * @return string
     */
    public function getOrganizationNo()
    {
        return $this->organizationNo;
    }

    /**
     * @param string $organizationNo
     */
    public function setOrganizationNo($organizationNo)
    {
        $this->organizationNo = $organizationNo;
    }

    /**
     * @return string
     */
    public function getOrganizationCode()
    {
        return $this->organizationCode;
    }

    /**
     * @param string $organizationCode
     */
    public function setOrganizationCode($organizationCode)
    {
        $this->organizationCode = $organizationCode;
    }

    /**
     * @return string
     */
    public function getOrgNameKanji()
    {
        return $this->orgNameKanji;
    }

    /**
     * @param string $orgNameKanji
     */
    public function setOrgNameKanji($orgNameKanji)
    {
        $this->orgNameKanji = $orgNameKanji;
    }

    /**
     * @return string
     */
    public function getOrgNameKana()
    {
        return $this->orgNameKana;
    }

    /**
     * @param string $orgNameKana
     */
    public function setOrgNameKana($orgNameKana)
    {
        $this->orgNameKana = $orgNameKana;
    }

    /**
     * @return string
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param string $department
     */
    public function setDepartment($department)
    {
        $this->department = $department;
    }

    /**
     * @return string
     */
    public function getSchoolDivision()
    {
        return $this->schoolDivision;
    }

    /**
     * @param string $schoolDivision
     */
    public function setSchoolDivision($schoolDivision)
    {
        $this->schoolDivision = $schoolDivision;
    }

    /**
     * @return int
     */
    public function getFlagRegister()
    {
        return $this->flagRegister;
    }

    /**
     * @param int $flagRegister
     */
    public function setFlagRegister($flagRegister)
    {
        $this->flagRegister = $flagRegister;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param string $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * @return string
     */
    public function getExamLocation()
    {
        return $this->examLocation;
    }

    /**
     * @param string $examLocation
     */
    public function setExamLocation($examLocation)
    {
        $this->examLocation = $examLocation;
    }

    /**
     * @return string
     */
    public function getExamLand()
    {
        return $this->examLand;
    }

    /**
     * @param string $examLand
     */
    public function setExamLand($examLand)
    {
        $this->examLand = $examLand;
    }

    /**
     * @return string
     */
    public function getOfficerName()
    {
        return $this->officerName;
    }

    /**
     * @param string $officerName
     */
    public function setOfficerName($officerName)
    {
        $this->officerName = $officerName;
    }

    /**
     * @return string
     */
    public function getCityCode()
    {
        return $this->cityCode;
    }

    /**
     * @param string $cityCode
     */
    public function setCityCode($cityCode)
    {
        $this->cityCode = $cityCode;
    }

    /**
     * @return int
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param int $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @param string $address1
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;
    }

    /**
     * @return string
     */
    public function getStateCode()
    {
        return $this->stateCode;
    }

    /**
     * @param string $stateCode
     */
    public function setStateCode($stateCode)
    {
        $this->stateCode = $stateCode;
    }

    /**
     * @return string
     */
    public function getTownCode()
    {
        return $this->townCode;
    }

    /**
     * @param string $townCode
     */
    public function setTownCode($townCode)
    {
        $this->townCode = $townCode;
    }

    /**
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @param string $address2
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getTelNo()
    {
        return $this->telNo;
    }

    /**
     * @param string $telNo
     */
    public function setTelNo($telNo)
    {
        $this->telNo = $telNo;
    }

    /**
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * @param string $fax
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
    }

    /**
     * @return string
     */
    public function getPasscode()
    {
        return $this->passcode;
    }

    /**
     * @param string $passcode
     */
    public function setPasscode($passcode)
    {
        $this->passcode = $passcode;
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
     * @return string
     */
    public function getReceptionTime()
    {
        return $this->receptionTime;
    }

    /**
     * @param string $receptionTime
     */
    public function setReceptionTime($receptionTime)
    {
        $this->receptionTime = $receptionTime;
    }

}