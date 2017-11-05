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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\UserRepository")
 * @ORM\Table(name="User")
 */
class User extends Common
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
     * Foreing key reference to Role
     * @ORM\Column(type="integer", name="RoleId", nullable=true)
     *
     * @var integer
     */
    protected $roleId;

    /* Property */
    /**
     * @ORM\Column(type="string", name="UserID", length=100, nullable=false)
     *
     * @var string
     */
    protected $userId;

    /**
     * @ORM\Column(type="string", name="Password", length=100, nullable=false)
     *
     * @var string
     */
    protected $password;

    /**
     * @ORM\Column(type="string", name="OldPasswordFirst", length=100, nullable=true)
     *
     * @var string
     */
    protected $oldPasswordFirst;

    /**
     * @ORM\Column(type="string", name="OldPasswordSecond", length=100, nullable=true)
     *
     * @var string
     */
    protected $oldPasswordSecond;

    /**
     * @ORM\Column(type="string", name="OrganizationNo", length=50, nullable=true)
     *
     * @var string
     */
    protected $organizationNo;

    /**
     * @ORM\Column(type="string", name="EmailAddress", length=100, nullable=true)
     *
     * @var string
     */
    protected $emailAddress;

    /**
     * @ORM\Column(type="string", name="FirstNameKanji",length=250, nullable=true)
     *
     * @var string
     */
    protected $firstNameKanji;

    /**
     * @ORM\Column(type="string", name="LastNameKanji", length=250, nullable=true)
     *
     * @var string
     */
    protected $lastNameKanji;

    /**
     * @ORM\Column(type="integer", name="CountLoginFailure", nullable=true)
     *
     * @var integer
     */
    protected $countLoginFailure;

    /**
     * @ORM\Column(type="smallint", name="Announcement", nullable=true)
     *
     * @var integer
     */
    protected $Announcement;

    /**
     * @ORM\Column(type="smallint", name="FirstLogin", nullable=true)
     *
     * @var integer
     */
    protected $firstLogin;

    /**
     * @ORM\Column(type="boolean", name="AgreePolicy", options={"default":0})
     *
     * @var boolean
     */
    protected $agreePolicy;

    /* Relationship */
    /**
     * @ORM\ManyToOne(targetEntity="Role")
     * @ORM\JoinColumn(name="RoleId", referencedColumnName="id")
     *
     * @var integer
     */
    protected $role;

    /**
     * @ORM\ManyToOne(targetEntity="Organization")
     * @ORM\JoinColumn(name="OrganizationId", referencedColumnName="id")
     */
    protected $organization;

    /**
     * @ORM\Column(type="smallint", name="FirstSendPass", options={"default":0})
     *
     * @var integer
     */
    protected $firstSendPass;

    /**
     * @ORM\Column(type="string", name="ServiceType",length=50, nullable=true)
     *
     * @var string
     */
    protected $serviceType;
    
    /**
     * @ORM\Column(type="smallint", name="StatusInit", options={"default":0})
     *
     * @var integer
     */
    protected $statusInit = 0;


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
     * @return string
     */
    public function getServiceType()
    {
        return $this->serviceType;
    }

    /**
     *
     * @param string $serviceType
     */
    public function setServiceType($serviceType)
    {
        $this->serviceType = $serviceType;
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
     * @return int
     */
    public function getRoleId()
    {
        return $this->roleId;
    }

    /**
     *
     * @param int $roleId
     */
    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;
    }

    /**
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     *
     * @param string $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     *
     * @return string
     */
    public function getOldPasswordFirst()
    {
        return $this->oldPasswordFirst;
    }

    /**
     *
     * @param string $oldPasswordFirst
     */
    public function setOldPasswordFirst($oldPasswordFirst)
    {
        $this->oldPasswordFirst = $oldPasswordFirst;
    }

    /**
     *
     * @return string
     */
    public function getOldPasswordSecond()
    {
        return $this->oldPasswordSecond;
    }

    /**
     *
     * @param string $oldPasswordSecond
     */
    public function setOldPasswordSecond($oldPasswordSecond)
    {
        $this->oldPasswordSecond = $oldPasswordSecond;
    }

    /**
     *
     * @return string
     */
    public function getOrganizationNo()
    {
        return $this->organizationNo;
    }

    /**
     *
     * @param string $organizationNo
     */
    public function setOrganizationNo($organizationNo)
    {
        $this->organizationNo = $organizationNo;
    }

    /**
     *
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     *
     * @param string $emailAddress
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
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
     * @return int
     */
    public function getCountLoginFailure()
    {
        return $this->countLoginFailure;
    }

    /**
     *
     * @param int $countLoginFailure
     */
    public function setCountLoginFailure($countLoginFailure)
    {
        $this->countLoginFailure = $countLoginFailure;
    }

    /**
     *
     * @return int
     */
    public function getAnnouncement()
    {
        return $this->Announcement;
    }

    /**
     *
     * @param int $Announcement
     */
    public function setAnnouncement($Announcement)
    {
        $this->Announcement = $Announcement;
    }

    /**
     *
     * @return int
     */
    public function getFirstLogin()
    {
        return $this->firstLogin;
    }

    /**
     *
     * @param int $firstLogin
     */
    public function setFirstLogin($firstLogin)
    {
        $this->firstLogin = $firstLogin;
    }

    /**
     *
     * @return int
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     *
     * @param int $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    /* Getter and Setter */
    /**
     *
     * @param User $user
     * @param string $password
     */
    public static function hashPassword($user, $password)
    {
        return ($user->getPassword() === md5($password.'FPT'));
    }

    /**
     * @return boolean
     */
    public function getAgreePolicy()
    {
        return $this->agreePolicy;
    }

    /**
     * @param boolean $agreePolicy
     */
    public function setAgreePolicy($agreePolicy)
    {
        $this->agreePolicy = $agreePolicy;
    }

    /**
     *
     * @return int
     */
    public function getFirstSendPass()
    {
        return $this->firstSendPass;
    }

    /**
     *
     * @param int $firstSendPass
     */
    public function setFirstSendPass($firstSendPass)
    {
        $this->firstSendPass = $firstSendPass;
    }

    public function getStatusInit() {
        return $this->statusInit;
    }

    public function setStatusInit($statusInit) {
        $this->statusInit = $statusInit;
    }

    /**
     * static function
     *
     * @param string $password
     * @return string
     */
    public static function generatePassword($password)
    {
        return md5($password.'FPT');
    }
    


}
