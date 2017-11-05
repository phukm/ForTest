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
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\PupilRepository")
 * @ORM\Table(name="Pupil")
 */
class Pupil extends Common
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
     * Foreing key reference to OrgSchoolYear
     * @ORM\Column(type="integer", name="OrgSchoolYearId", nullable=true)
     *
     * @var integer
     */
    protected $orgSchoolYearId;

    /**
     * Foreing key reference to ClassJ
     * @ORM\Column(type="integer", name="ClassId", nullable=true)
     *
     * @var integer
     */
    protected $classId;
    /**
     *
     * @ORM\Column(type="integer", name="PupilID", nullable=true)
     *
     * @var integer
     */
    protected $pupilID;

    /* Property */
    /**
     * @ORM\Column(type="string", name="EinaviId", length=50, nullable=true)
     *
     * @var string
     */
    protected $einaviId;

    /**
     * @ORM\Column(type="string", name="PersonalId", length=50, nullable=true)
     *
     * @var string
     */
    protected $personalId;

    /**
     * @ORM\Column(type="integer", name="Year", nullable=true)
     *
     * @var integer
     */
    protected $year;

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
     * @ORM\Column(type="smallint", name="Gender")
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
     * @ORM\Column(type="integer", name="PhoneNo", length=20, nullable=true)
     *
     * @var integer
     */
    protected $PhoneNo;

    /**
     * @ORM\Column(type="string", name="Email", length=100, nullable=true)
     *
     * @var string
     */
    protected $email;

    /**
     * @ORM\Column(type="string", name="EikenPassword", length=100, nullable=true)
     *
     * @var string
     */
    protected $eikenPassword;

    /* Relationship */
    /**
     * @ORM\ManyToOne(targetEntity="Organization")
     * @ORM\JoinColumn(name="OrganizationId", referencedColumnName="id")
     */
    protected $organization;

    /**
     * @ORM\ManyToOne(targetEntity="OrgSchoolYear")
     * @ORM\JoinColumn(name="OrgSchoolYearId", referencedColumnName="id")
     */
    protected $orgSchoolYear;

    /**
     * @ORM\ManyToOne(targetEntity="ClassJ")
     * @ORM\JoinColumn(name="ClassId", referencedColumnName="id")
     */
    protected $class;

    /**
     * @ORM\PrePersist
     */
//     public function init()
//     {
//         parent::init();
//         $this->serialId = '-1';
//     }

    /**
     * @ORM\PostPersist
     */
    // public function postPersist() {
    // $this->serialId = $this->id;
    // }

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
     * @return bigint
     */
    public function getSerialId()
    {
        return $this->id;
    }

    /**
     *
     * @param bigint $serialId
     */
    public function setSerialId($serialId)
    {
//        $this->serialId = $serialId;
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
     * @return int
     */
    public function getPupilID()
    {
        return $this->pupilID;
    }

    /**
     *
     * @param int $number
     */
    public function setPupilID($pupilID)
    {
        $this->pupilID = $pupilID;
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
     * @return \Application\Entity\Organization
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
     * @return \Application\Entity\ClassJ
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
     * @return string
     */
    public function getEinaviId()
    {
        return $this->einaviId;
    }

    /**
     *
     * @param string $einaviId
     */
    public function setEinaviId($einaviId)
    {
        $this->einaviId = $einaviId;
    }

    /**
     *
     * @return string
     */
    public function getPersonalId()
    {
        return $this->personalId;
    }

    /**
     *
     * @param string $personalId
     */
    public function setPersonalId($personalId)
    {
        $this->personalId = $personalId;
    }
    /* Getter and Setter */
    
    function toArray($format = 'Y/m/d'){
        $properties = get_object_vars($this);
        $return =  array();
        foreach ($properties as $key => $pr){
            if($pr instanceof \DateTime){
                $return[$key] = $pr->format($format);
            }
            if(is_object($pr)){
                continue;
            }
            $return[$key] = $pr;
        }
        return $return;
    }
}