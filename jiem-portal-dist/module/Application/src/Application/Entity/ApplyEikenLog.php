<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\ApplyEikenLogRepository")
 * @ORM\Table(name="ApplyEikenLog")
 */
class ApplyEikenLog extends Common
{
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
     * @ORM\Column(type="string", name="OrganizationNo", length=50, nullable=true)
     *
     * @var string
     */
    protected $organizationNo;
    
    /**
     * @ORM\Column(type="string", name="OrganizationName", length=250, nullable=true)
     *
     * @var string
     */
    protected $organizationName;
    
    /**
     * @ORM\Column(type="string", name="Action", length=50, nullable=true)
     *
     * @var string
     */
    protected $action;
    
    /**
     * @ORM\Column(type="datetime", name="LogTime", nullable=true)
     *
     * @var datetime
     */
    protected $logTime;
    
    /**
     * @ORM\Column(type="string", name="MainDetail", length=250, nullable=true)
     *
     * @var string
     */
    protected $mainDetail;
    
    /**
     * @ORM\Column(type="string", name="StandardDetail", length=250, nullable=true)
     *
     * @var string
     */
    protected $standardDetail;
    
    /**
     * @ORM\Column(type="string", name="RefundDetail", length=250, nullable=true)
     *
     * @var string
     */
    protected $refundDetail;
    
    /**
     * @ORM\Column(type="string", name="UserId", length=250, nullable=true)
     *
     * @var string
     */
    protected $userId;
    
    /**
     * @ORM\Column(type="string", name="UserName", length=250, nullable=true)
     *
     * @var string
     */
    protected $userName;
    
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
     * GETTER
     */
    function getOrganizationId() {
        return $this->organizationId;
    }

    function getEikenScheduleId() {
        return $this->eikenScheduleId;
    }

    function getOrganizationNo() {
        return $this->organizationNo;
    }

    function getOrganizationName() {
        return $this->organizationName;
    }

    function getAction() {
        return $this->action;
    }

    function getLogTime() {
        return $this->logTime;
    }

    function getMainDetail() {
        return $this->mainDetail;
    }

    function getStandardDetail() {
        return $this->standardDetail;
    }

    function getRefundDetail() {
        return $this->refundDetail;
    }
    
    function getUserId() {
        return $this->userId;
    }

    function getUserName() {
        return $this->userName;
    }

    function getOrganization() {
        return $this->organization;
    }

    function getEikenSchedule() {
        return $this->eikenSchedule;
    }

    /**
     * SETTER
     */
    function setOrganizationId($organizationId) {
        $this->organizationId = $organizationId;
    }

    function setEikenScheduleId($eikenScheduleId) {
        $this->eikenScheduleId = $eikenScheduleId;
    }

    function setOrganizationNo($organizationNo) {
        $this->organizationNo = $organizationNo;
    }

    function setOrganizationName($organizationName) {
        $this->organizationName = $organizationName;
    }

    function setAction($action) {
        $this->action = $action;
    }

    function setLogTime(datetime $logTime) {
        $this->logTime = $logTime;
    }

    function setMainDetail($mainDetail) {
        $this->mainDetail = $mainDetail;
    }

    function setStandardDetail($standardDetail) {
        $this->standardDetail = $standardDetail;
    }

    function setRefundDetail($refundDetail) {
        $this->refundDetail = $refundDetail;
    }
    
    function setUserId($userId) {
        $this->userId = $userId;
    }

    function setUserName($userName) {
        $this->userName = $userName;
    }

    function setOrganization($organization) {
        $this->organization = $organization;
    }

    function setEikenSchedule($eikenSchedule) {
        $this->eikenSchedule = $eikenSchedule;
    }
}

