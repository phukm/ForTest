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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\PaymentInfoRepository")
 * @ORM\Table(name="PaymentInfo")
 */
class PaymentInfo extends Common
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
     * Foreing key reference to EikenSchedule
     * @ORM\Column(type="integer", name="EikenScheduleId", nullable=true)
     *
     * @var integer
     */
    protected $eikenScheduleId;

    /* Property */
    
    /**
     * @ORM\Column(type="string", name="SiteCode", length=50, nullable=true)
     *
     * @var string
     */
    protected $siteCode;

    /**
     * @ORM\Column(type="string", name="MailAddress", length=100, nullable=true)
     *
     * @var string
     */
    protected $mailAddress;

    /**
     * @ORM\Column(type="string", name="Name", length=250, nullable=true)
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="datetime", name="Deadline", nullable=true)
     */
    protected $deadLine;

    /**
     * @ORM\Column(type="smallint", name="PaymentStatus", nullable=true)
     */
    protected $paymentStatus;

    /* Relationship */
    /**
     * @ORM\ManyToOne(targetEntity="Pupil")
     * @ORM\JoinColumn(name="PupilId", referencedColumnName="id")
     */
    protected $pupil;

    /**
     * @ORM\ManyToOne(targetEntity="EikenSchedule")
     * @ORM\JoinColumn(name="EikenScheduleId", referencedColumnName="id")
     */
    protected $eikenSchedule;

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
     * @return string
     */
    public function getSiteCode()
    {
        return $this->siteCode;
    }

    /**
     * @param string $siteCode
     */
    public function setSiteCode($siteCode)
    {
        $this->siteCode = $siteCode;
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
    public function getPaymentStatus()
    {
        return $this->paymentStatus;
    }

    /**
     * @param mixed $paymentStatus
     */
    public function setPaymentStatus($paymentStatus)
    {
        $this->paymentStatus = $paymentStatus;
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
    /* Getter and Setter */

}