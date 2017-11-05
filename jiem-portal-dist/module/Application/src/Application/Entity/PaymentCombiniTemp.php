<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Paymentcombinitemp
 *
 * @ORM\Table(name="PaymentCombiniTemp")
 * @ORM\Entity
 */
class PaymentCombiniTemp
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
     * @ORM\Column(name="OrgId", type="integer", nullable=false)
     */
    private $orgId;

    /**
     * @var integer
     *
     * @ORM\Column(name="ClassId", type="integer", nullable=false)
     */
    private $classId;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="PupilId", type="integer", nullable=false)
     */
    private $pupilId;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="PaymentId", type="integer", nullable=true)
     */
    private $paymentId;

    /**
     * @var string
     *
     * @ORM\Column(name="PupilName", type="string", length=255, nullable=true)
     */
    private $pupilName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="Deadline", type="datetime", nullable=true)
     */
    private $deadline;

    /**
     * @var integer
     *
     * @ORM\Column(name="EikenScheduleId", type="integer", nullable=true)
     */
    private $eikenScheduleId;

    /**
     * @var integer
     *
     * @ORM\Column(name="EikenLevelId", type="integer", nullable=true)
     */
    private $eikenLevelId;

    /**
     * @var string
     *
     * @ORM\Column(name="TelNo", type="string", length=50, nullable=true)
     */
    private $telNo;

    /**
     * @var string
     *
     * @ORM\Column(name="ProductName", type="string", length=50, nullable=true)
     */
    private $productName;

    /**
     * @var integer
     *
     * @ORM\Column(name="Price", type="integer", nullable=true)
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="OrderId", type="string", length=50, nullable=true)
     */
    private $orderId;
    
    /**
     * @var string
     *
     * @ORM\Column(name="ReceiptNo", type="string", length=10, nullable=true)
     */
    private $receiptNo;
    
    /**
     * @var string
     *
     * @ORM\Column(name="OrderResultCode", type="string", length=50, nullable=true)
     */
    private $orderResultCode;
    
    /**
     * @var string
     *
     * @ORM\Column(name="OrderResultInfo", type="string", length=255, nullable=true)
     */
    private $orderResultInfo;
    
    /**
     * @ORM\Column(type="boolean", name="IsCompleted",options={"default":0})
     * @var boolean
     */
    private $isCompleted = 0;
            
    
    function getReceiptNo() {
        return $this->receiptNo;
    }

    function getOrderResultCode() {
        return $this->orderResultCode;
    }

    function getOrderResultInfo() {
        return $this->orderResultInfo;
    }

    function setReceiptNo($receiptNo) {
        $this->receiptNo = $receiptNo;
    }

    function setOrderResultCode($orderResultCode) {
        $this->orderResultCode = $orderResultCode;
    }

    function setOrderResultInfo($orderResultInfo) {
        $this->orderResultInfo = $orderResultInfo;
    }

    function getPupilId() {
        return $this->pupilId;
    }

    function setPupilId($pupilId) {
        $this->pupilId = $pupilId;
    }

    function getId() {
        return $this->id;
    }

    function getOrgId() {
        return $this->orgId;
    }

    function getClassId() {
        return $this->classId;
    }

    function getPupilName() {
        return $this->pupilName;
    }

    function getDeadline() {
        return $this->deadline;
    }

    function getEikenScheduleId() {
        return $this->eikenScheduleId;
    }

    function getEikenLevelId() {
        return $this->eikenLevelId;
    }

    function getTelNo() {
        return $this->telNo;
    }

    function getProductName() {
        return $this->productName;
    }

    function getPrice() {
        return $this->price;
    }

    function getOrderId() {
        return $this->orderId;
    }

    function setOrgId($orgId) {
        $this->orgId = $orgId;
    }

    function setClassId($classId) {
        $this->classId = $classId;
    }

    function setPupilName($pupilName) {
        $this->pupilName = $pupilName;
    }

    function setDeadline(\DateTime $deadline) {
        $this->deadline = $deadline;
    }

    function setEikenScheduleId($eikenScheduleId) {
        $this->eikenScheduleId = $eikenScheduleId;
    }

    function setEikenLevelId($eikenLevelId) {
        $this->eikenLevelId = $eikenLevelId;
    }

    function setTelNo($telNo) {
        $this->telNo = $telNo;
    }

    function setProductName($productName) {
        $this->productName = $productName;
    }

    function setPrice($price) {
        $this->price = $price;
    }

    function setOrderId($orderId) {
        $this->orderId = $orderId;
    }
    
    function getIsCompleted() {
        return $this->isCompleted;
    }

    function setIsCompleted($isCompleted) {
        $this->isCompleted = $isCompleted;
    }
    
    public function getPaymentId() {
        return $this->paymentId;
    }

    public function setPaymentId($paymentId) {
        $this->paymentId = $paymentId;
    }

    function toArray(){
        return get_object_vars($this);
    }
    
}
