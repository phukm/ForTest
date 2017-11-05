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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\EinaviInfoRepository")
 * @ORM\Table(name="EinaviInfo")
 */
class EinaviInfo extends Common
{

    /* Foreing key */
    /**
     * Foreing key reference to Pupil
     * @ORM\Column(type="integer", name="PupilId", nullable=true)
     *
     * @var integer
     */
    protected $pupilId;
    /* Property */
    /**
     * @ORM\Column(type="string", name="EinaviId", length=50, nullable=true)
     *
     * @var string
     */
    protected $einaviId;

    /**
     * @ORM\Column(type="string", name="NameKanji", length=100, nullable=true)
     *
     * @var string
     */
    protected $nameKanji;
    /**
     * @ORM\Column(type="boolean", name="Gender", nullable=true)
     *
     * @var boolean
     */
    protected $gender;
    /**
     * @ORM\Column(type="datetime", name="BirthDay", length=50, nullable=true)
     *
     * @var datetime
     */
    protected $birthDay;
    /**
     * @ORM\Column(type="string", name="PostCode1", length=50, nullable=true)
     *
     * @var string
     */
    protected $postCode1;
    /**
     * @ORM\Column(type="string", name="PostCode2", length=50, nullable=true)
     *
     * @var string
     */
    protected $postCode2;
    /**
     * @ORM\Column(type="string", name="EmailAdd", length=100, nullable=true)
     *
     * @var string
     */
    protected $emailAdd;
    /**
     * @ORM\Column(type="string", name="Password1", length=100, nullable=true)
     *
     * @var string
     */
    protected $password1;
    /**
     * @ORM\Column(type="string", name="Password2", length=100, nullable=true)
     *
     * @var string
     */
    protected $password2;
    /**
     * @ORM\Column(type="boolean", name="Receive",  nullable=true)
     *
     * @var boolean
     */
    protected $receive;

    /**
     * @ORM\Column(type="string", name="Attestation", length=50, nullable=true)
     *
     * @var string
     */
    protected $attestation;
    /**
     * @ORM\Column(type="string", name="PersonalId", length=50, nullable=true)
     *
     * @var string
     */
    protected $personalId;
    /**
     * @ORM\ManyToOne(targetEntity="Pupil")
     * @ORM\JoinColumn(name="PupilId", referencedColumnName="id")
     */
    protected $pupil;
    /**
     * @return string
     */
    public function getEinaviId()
    {
        return $this->einaviId;
    }

    /**
     * @param string $einaviId
     */
    public function setEinaviId($einaviId)
    {
        $this->einaviId = $einaviId;
    }

    /**
     * @return string
     */
    public function getNameKanji()
    {
        return $this->nameKanji;
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
     * @param string $nameKanji
     */
    public function setNameKanji($nameKanji)
    {
        $this->nameKanji = $nameKanji;
    }

    /**
     * @return boolean
     */
    public function isGender()
    {
        return $this->gender;
    }

    /**
     * @param boolean $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return datetime
     */
    public function getBirthDay()
    {
        return $this->birthDay;
    }

    /**
     * @param datetime $birthDay
     */
    public function setBirthDay($birthDay)
    {
        $this->birthDay = $birthDay;
    }

    /**
     * @return string
     */
    public function getPostCode1()
    {
        return $this->postCode1;
    }

    /**
     * @param string $postCode1
     */
    public function setPostCode1($postCode1)
    {
        $this->postCode1 = $postCode1;
    }

    /**
     * @return string
     */
    public function getPostCode2()
    {
        return $this->postCode2;
    }

    /**
     * @param string $postCode2
     */
    public function setPostCode2($postCode2)
    {
        $this->postCode2 = $postCode2;
    }

    /**
     * @return string
     */
    public function getEmailAdd()
    {
        return $this->emailAdd;
    }

    /**
     * @param string $emailAdd
     */
    public function setEmailAdd($emailAdd)
    {
        $this->emailAdd = $emailAdd;
    }

    /**
     * @return string
     */
    public function getPassword1()
    {
        return $this->password1;
    }

    /**
     * @param string $password1
     */
    public function setPassword1($password1)
    {
        $this->password1 = $password1;
    }

    /**
     * @return string
     */
    public function getPassword2()
    {
        return $this->password2;
    }

    /**
     * @param string $password2
     */
    public function setPassword2($password2)
    {
        $this->password2 = $password2;
    }

    /**
     * @return boolean
     */
    public function isReceive()
    {
        return $this->receive;
    }

    /**
     * @param boolean $receive
     */
    public function setReceive($receive)
    {
        $this->receive = $receive;
    }

    /**
     * @return string
     */
    public function getAttestation()
    {
        return $this->attestation;
    }

    /**
     * @param string $attestation
     */
    public function setAttestation($attestation)
    {
        $this->attestation = $attestation;
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
    /* Relationship */

    /* Getter and Setter */

}