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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\ApplyEikenPersonalInfoRepository")
 * @ORM\Table(name="ApplyEikenPersonalInfo")
 */
class ApplyEikenPersonalInfo extends Common
{

    /* Foreing key */
    /**
     * @ORM\Column(type="integer", name ="OrganizationId", nullable=true)
     *
     * @var integer
     */
    protected $organizationId;

    /**
     * @ORM\Column(type="integer", name ="ClassId", nullable=true)
     *
     * @var integer
     */
    protected $classId;

    /**
     * @ORM\Column(type="integer", name ="OrgSchoolYearId", nullable=true)
     *
     * @var integer
     */
    protected $orgSchoolYearId;

    /**
     * @ORM\Column(type="integer", name ="EikenScheduleId", nullable=true)
     *
     * @var integer
     */
    protected $eikenScheduleId;
    /**
     * @ORM\Column(type="integer", name ="PupilId", nullable=true)
     *
     * @var integer
     */
    protected $pupilId;

    /* Property */

    /**
     * @ORM\Column(type="string", name="SerialID", length=50, nullable=true)
     *
     * @var string
     */
    protected $serialId;

    /**
     * @ORM\Column(type="string", name="RecommendedLevel", length=50, nullable=true)
     *
     * @var string
     */
    protected $recommendedLevel;
    /**
     * @ORM\Column(type="string", name="ClassName",length=250, nullable=true)
     * @var string
     */
    protected $className;

    /**
     * @ORM\Column(type="string", name="OrgSchoolYearName",length=250, nullable=true)
     * @var string
     */
    protected $orgSchoolYearName;

    /**
     * @ORM\Column(type="string", name="RegisterLevel", length=50, nullable=true)
     *
     * @var string
     */
    protected $registerLevel;

    /**
     * @ORM\Column(type="integer", name="Number", nullable=true)
     *
     * @var integer
     */
    protected $number;

    /**
     * @ORM\Column(type="string", name="FirstNameKanji", length=100, nullable=true)
     *
     * @var string
     */
    protected $firstNameKanji;

    /**
     * @ORM\Column(type="string", name="LastNameKanji", length=100, nullable=true)
     *
     * @var string
     */
    protected $lastNameKanji;

    /**
     * @ORM\Column(type="string", name="FirstNameKana", length=100, nullable=true)
     *
     * @var string
     */
    protected $firstNameKana;

    /**
     * @ORM\Column(type="string", name="LastNameKana", length=100, nullable=true)
     *
     * @var string
     */
    protected $lastNameKana;

    /**
     * @ORM\Column(type="string", name="FirstNameAlpha", length=100, nullable=true)
     *
     * @var string
     */
    protected $firstNameAlpha;

    /**
     * @ORM\Column(type="string", name="LastNameAlpha", length=100, nullable=true)
     *
     * @var string
     */
    protected $lastNameAlpha;

    /**
     * @ORM\Column(type="datetime", name="Birthday", nullable=true)
     */
    protected $birthday;

    /**
     * @ORM\Column(type="smallint", name="Gender", nullable=true)
     *
     * @var integer
     */
    protected $gender;

    /**
     * @ORM\Column(type="string", name="EikenID", length=50, nullable=true)
     *
     * @var string
     */
    protected $eikenId;

    /**
     * @ORM\Column(type="string", name="PhoneNo", length=50, nullable=true)
     *
     * @var string
     */
    protected $PhoneNo;

    /**
     * @ORM\Column(type="string", name="Email", length=100, nullable=true)
     *
     * @var string
     */
    protected $email;

    /**
     * @ORM\Column(type="string", name="District", length=100, nullable=true)
     *
     * @var string
     */
    protected $district;

    /**
     * @ORM\Column(type="string", name="Town", length=100, nullable=true)
     *
     * @var string
     */
    protected $town;

    /**
     * @ORM\Column(type="string", name="HouseNumber", length=100, nullable=true)
     *
     * @var string
     */
    protected $houseNumber;

    /**
     * @ORM\Column(type="string", name="BuildingName", length=100, nullable=true)
     *
     * @var string
     */
    protected $buildingName;

    /**
     * @ORM\Column(type="string", name="PostalCode", length=20, nullable=true)
     *
     * @var string
     */
    protected $postalCode;

    /**
     * @ORM\Column(type="string", name="JobCode", length=50, nullable=true)
     *
     * @var string
     */
    protected $jobCode;

    /**
     * @ORM\Column(type="string", name="SchoolCode", length=50, nullable=true)
     *
     * @var string
     */
    protected $schoolCode;

    /**
     * @ORM\Column(type="string", name="EikenPassword", length=100, nullable=true)
     *
     * @var string
     */
    protected $eikenPassword;

    /**
     * /**
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumn(name="CityId", referencedColumnName="id")
     */
    protected $city;

    /**
     * @ORM\Column(type="boolean", name="IsSateline", options={"default":0})
     *
     * @var boolean
     */
    protected $isSateline;

    /* Relationship */
    /**
     * @ORM\ManyToOne(targetEntity="Organization")
     * @ORM\JoinColumn(name="OrganizationId", referencedColumnName="id")
     */
    protected $organization;

    /**
     * @ORM\ManyToOne(targetEntity="ClassJ")
     * @ORM\JoinColumn(name="ClassId", referencedColumnName="id")
     */
    protected $class;

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
     * @ORM\ManyToOne(targetEntity="Pupil")
     * @ORM\JoinColumn(name="PupilId", referencedColumnName="id")
     */
    protected $pupil;
    
        
    /**
     * @ORM\Column(type="string", name="SchoolType", nullable=true)
     *
     * @var string
     */
    protected $schoolType;

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
    public function getClassId()
    {
        return $this->classId;
    }

    /**
     *
     * @param int $classId
     */
    public function setClassId($classId)
    {
        $this->classId = $classId;
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
     * @return string
     */
    public function getSerialId()
    {
        return $this->serialId;
    }

    /**
     *
     * @param string $serialId
     */
    public function setSerialId($serialId)
    {
        $this->serialId = $serialId;
    }

    /**
     *
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     *
     * @param int $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     *
     * @return string
     */
    public function getFirstNameKanji()
    {
        return $this->firstNameKanji;
    }

    /**
     *
     * @param string $firstNameKanji
     */
    public function setFirstNameKanji($firstNameKanji)
    {
        $this->firstNameKanji = $firstNameKanji;
    }

    /**
     *
     * @return string
     */
    public function getLastNameKanji()
    {
        return $this->lastNameKanji;
    }

    /**
     *
     * @param string $lastNameKanji
     */
    public function setLastNameKanji($lastNameKanji)
    {
        $this->lastNameKanji = $lastNameKanji;
    }

    /**
     *
     * @return string
     */
    public function getFirstNameKana()
    {
        return $this->firstNameKana;
    }

    /**
     *
     * @param string $firstNameKana
     */
    public function setFirstNameKana($firstNameKana)
    {
        $this->firstNameKana = $firstNameKana;
    }

    /**
     *
     * @return string
     */
    public function getLastNameKana()
    {
        return $this->lastNameKana;
    }

    /**
     *
     * @param string $lastNameKana
     */
    public function setLastNameKana($lastNameKana)
    {
        $this->lastNameKana = $lastNameKana;
    }

    /**
     *
     * @return string
     */
    public function getFirstNameAlpha()
    {
        return $this->firstNameAlpha;
    }

    /**
     *
     * @param string $firstNameAlpha
     */
    public function setFirstNameAlpha($firstNameAlpha)
    {
        $this->firstNameAlpha = $firstNameAlpha;
    }

    /**
     *
     * @return string
     */
    public function getLastNameAlpha()
    {
        return $this->lastNameAlpha;
    }

    /**
     *
     * @param string $lastNameAlpha
     */
    public function setLastNameAlpha($lastNameAlpha)
    {
        $this->lastNameAlpha = $lastNameAlpha;
    }

    /**
     *
     * @return mixed
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     *
     * @param mixed $birthday
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }

    /**
     *
     * @return int
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     *
     * @param int $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     *
     * @return string
     */
    public function getEikenId()
    {
        return $this->eikenId;
    }

    /**
     *
     * @param string $eikenId
     */
    public function setEikenId($eikenId)
    {
        $this->eikenId = $eikenId;
    }

    /**
     *
     * @return int
     */
    public function getPhoneNo()
    {
        return $this->PhoneNo;
    }

    /**
     *
     * @param int $PhoneNo
     */
    public function setPhoneNo($PhoneNo)
    {
        $this->PhoneNo = $PhoneNo;
    }

    /**
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     *
     * @return string
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     *
     * @param string $district
     */
    public function setDistrict($district)
    {
        $this->district = $district;
    }

    /**
     *
     * @return string
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     *
     * @param string $town
     */
    public function setTown($town)
    {
        $this->town = $town;
    }

    /**
     *
     * @return string
     */
    public function getHouseNumber()
    {
        return $this->houseNumber;
    }

    /**
     *
     * @param string $houseNumber
     */
    public function setHouseNumber($houseNumber)
    {
        $this->houseNumber = $houseNumber;
    }

    /**
     *
     * @return string
     */
    public function getBuildingName()
    {
        return $this->buildingName;
    }

    /**
     *
     * @param string $buildingName
     */
    public function setBuildingName($buildingName)
    {
        $this->buildingName = $buildingName;
    }

    /**
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     *
     * @param string $postalCode
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /**
     *
     * @return string
     */
    public function getJobCode()
    {
        return $this->jobCode;
    }

    /**
     *
     * @param string $jobCode
     */
    public function setJobCode($jobCode)
    {
        $this->jobCode = $jobCode;
    }

    /**
     *
     * @return string
     */
    public function getSchoolCode()
    {
        return $this->schoolCode;
    }

    /**
     *
     * @param string $schoolCode
     */
    public function setSchoolCode($schoolCode)
    {
        $this->schoolCode = $schoolCode;
    }

    /**
     *
     * @return string
     */
    public function getEikenPassword()
    {
        return $this->eikenPassword;
    }

    /**
     *
     * @param string $eikenPassword
     */
    public function setEikenPassword($eikenPassword)
    {
        $this->eikenPassword = $eikenPassword;
    }


    /**
     *
     * @return boolean
     */
    public function isIsSateline()
    {
        return $this->isSateline;
    }

    /**
     *
     * @param boolean $isSateline
     */
    public function setIsSateline($isSateline)
    {
        $this->isSateline = $isSateline;
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
     * @return string
     */
    public function getRecommendedLevel()
    {
        return $this->recommendedLevel;
    }

    /**
     * @param string $recommendedLevel
     */
    public function setRecommendedLevel($recommendedLevel)
    {
        $this->recommendedLevel = $recommendedLevel;
    }

    /**
     * @return string
     */
    public function getRegisterLevel()
    {
        return $this->registerLevel;
    }

    /**
     * @param string $registerLevel
     */
    public function setRegisterLevel($registerLevel)
    {
        $this->registerLevel = $registerLevel;
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
    public function getClass()
    {
        return $this->class;
    }

    /**
     *
     * @param mixed $class
     */
    public function setClass($class)
    {
        $this->class = $class;
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
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param mixed $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * @return mixed
     */
    public function getOrgSchoolYearName()
    {
        return $this->orgSchoolYearName;
    }

    /**
     * @param mixed $orgSchoolYearName
     */
    public function setOrgSchoolYearName($orgSchoolYearName)
    {
        $this->orgSchoolYearName = $orgSchoolYearName;
    }
    
    function getSchoolType() {
        return $this->schoolType;
    }

    function setSchoolType($schoolType) {
        $this->schoolType = $schoolType;
    }



}