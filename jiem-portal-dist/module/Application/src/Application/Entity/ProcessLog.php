<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Processlog
 *
 * @ORM\Table(name="ProcessLog")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ProcessLog
{
    /**
     * @var integer
     *
     * @ORM\Column(name="Id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="OrgId", type="integer", nullable=true)
     */
    private $orgId;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="PupilId", type="integer", nullable=true)
     */
    private $pupilId;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="ScheduleId", type="integer", nullable=false)
     */
    private $scheduleId;

    /**
     * @var integer
     *
     * @ORM\Column(name="Total", type="integer", nullable=false)
     */
    private $total;

    /**
     * @var integer
     *
     * @ORM\Column(name="Active", type="integer", nullable=false)
     */
    private $active = '0';
    
    /**
     * @var integer
     *
     * @ORM\Column(name="IgnorePayment", type="integer", options={"default":0}, nullable=false)
     */
    private $ignorePayment = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="AdminInfo", type="text", nullable=true)
     */
    private $adminInfo;

    /**
     * @ORM\Column(type="boolean", name="IsError",options={"default":0})
     * @var boolean
     */
    private $isError = 0;
    
    /**
     * @ORM\Column(type="datetime", name="InsertAt", options={"default":"CURRENT_TIMESTAMP"})
     */
    protected $insertAt;
    
    /**
     * @ORM\Column(type="datetime", name="SendCombiniAt", nullable=true)
     */
    protected $sendCombiniAt;
    
    /**
     * @ORM\PrePersist
     */
    public function init()
    {
        $this->insertAt = new \DateTime("now");
    }
    
    function getScheduleId() {
        return $this->scheduleId;
    }

    function setScheduleId($scheduleId) {
        $this->scheduleId = $scheduleId;
    }

    function getId() {
        return $this->id;
    }

    function getOrgId() {
        return $this->orgId;
    }
    
    function getPupilId() {
        return $this->pupilId;
    }

    function getTotal() {
        return $this->total;
    }

    function getActive() {
        return $this->active;
    }

    function getAdminInfo() {
        return $this->adminInfo;
    }

    function getIsError() {
        return $this->isError;
    }

    function setOrgId($orgId) {
        $this->orgId = $orgId;
    }
    
    function setPupilId($pupilId) {
        $this->pupilId = $pupilId;
    }

    function setTotal($total) {
        $this->total = $total;
    }

    function setActive($active) {
        $this->active = $active;
    }

    function setAdminInfo($adminInfo) {
        $this->adminInfo = $adminInfo;
    }

    function setIsError($isError) {
        $this->isError = $isError;
    }
    
    public function getIgnorePayment() {
        return $this->ignorePayment;
    }

    public function setIgnorePayment($ignorePayment) {
        $this->ignorePayment = $ignorePayment;
    }

    public function isRunable(){
        return $this->total <= ($this->active + $this->ignorePayment);
    }
    
    public function setRunable(){
        $this->active = $this->total;
    }
    
    public function getInsertAt() {
        return $this->insertAt;
    }

    public function getSendCombiniAt() {
        return $this->sendCombiniAt;
    }

    public function setInsertAt($insertAt) {
        $this->insertAt = $insertAt;
    }

    public function setSendCombiniAt($sendCombiniAt) {
        $this->sendCombiniAt = $sendCombiniAt;
    }

}
