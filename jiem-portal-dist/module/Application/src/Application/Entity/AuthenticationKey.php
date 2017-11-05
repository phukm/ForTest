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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\AuthenticationKeyRepository")
 * @ORM\Table(name="AuthenticationKey")
 */
class AuthenticationKey extends Common
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
     * @ORM\Column(type="string", name="AuthenKey", length=50, nullable=true)
     *
     * @var string
     */
    protected $authenKey;

    /**
     * @ORM\Column(type="string", name="OrganizationNo", length=50, nullable=true)
     *
     * @var string
     */
    protected $organizationNo;

    /**
     * @ORM\Column(type="datetime", name="ExpireDate",nullable=true )
     */
    protected $expireDate;
    /**
     * Foreing key reference to EikenSchedule
     * @ORM\Column(type="integer", name="EikenScheduleId", nullable=true)
     *
     * @var integer
     */
    protected $eikenScheduleId;
    /**
     * @ORM\ManyToOne(targetEntity="EikenSchedule")
     * @ORM\JoinColumn(name="EikenScheduleId", referencedColumnName="id")
     */
    protected $eikenSchedule;

    /**
     * @ORM\Column(type="string", name="SessionId", length=50, nullable=true)
     *
     * @var string
     */
    protected $sessionId;

    /* Relationship */
    /**
     * @ORM\ManyToOne(targetEntity="Pupil")
     * @ORM\JoinColumn(name="PupilId", referencedColumnName="id")
     */
    protected $pupil;

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
     *
     * @return string
     */
    public function getAuthenKey()
    {
        return $this->authenKey;
    }

    /**
     *
     * @param string $authenKey
     */
    public function setAuthenKey($authenKey)
    {
        $this->authenKey = $authenKey;
    }

    /**
     *
     * @return string
     */
    public function getExpireDate()
    {
        return $this->expireDate;
    }

    /**
     *
     * @param string $authenKey
     */
    public function setExpireDate($expireDate)
    {
        $this->expireDate = $expireDate;
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
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param string $sessionId
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }
    /* Getter and Setter */
}