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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\IssuingPaymentRepository")
 * @ORM\Table(name="IssuingPayment")
 */
class IssuingPayment extends Common
{

    /* Foreing key */
    /**
     * Foreing key reference to EikenLevel
     * @ORM\Column(type="integer", name="EikenLevelId", nullable=true)
     *
     * @var integer
     */
    protected $eikenLevelId;

    /**
     * Foreing key reference to PaymentInfo
     * @ORM\Column(type="integer", name="PaymentInfoId", nullable=true)
     *
     * @var integer
     */
    protected $paymentInfoId;

    /* Property */
    /**
     *
     * /**
     * @ORM\Column(type="string", name="OrderID", length=50, nullable=true)
     *
     * @var string
     */
    protected $orderId;

    /**
     * @ORM\Column(type="string", name="TelNo", length=50, nullable=true)
     *
     * @var string
     */
    protected $telNo;

    /**
     * @ORM\Column(type="string", name="ProductName", length=50, nullable=true)
     *
     * @var string
     */
    protected $productName;

    /**
     * @ORM\Column(type="decimal", name="Price", nullable=true)
     */
    protected $price;

    /**
     * @ORM\Column(type="string", name="ReceiptNo", length=10, nullable=true)
     *
     * @var string
     */
    protected $receiptNo;

    /**
     * @ORM\Column(type="string", name="OrderResultCode", length=50, nullable=true)
     *
     * @var string
     */
    protected $orderResultCode;

    /**
     * @ORM\Column(type="string", name="OrderResultInfo", length=250, nullable=true)
     *
     * @var string
     */
    protected $orderResultInfo;

    /**
     * @ORM\Column(type="string", name="PaymentStatus", length=150, nullable=true)
     *
     * @var string
     */
    protected $paymentStatus;

    /* Relationship */
    /**
     * @ORM\ManyToOne(targetEntity="PaymentInfo")
     * @ORM\JoinColumn(name="PaymentInfoId", referencedColumnName="id")
     */
    protected $paymentInfo;

    /**
     * @ORM\ManyToOne(targetEntity="EikenLevel")
     * @ORM\JoinColumn(name="EikenLevelId", referencedColumnName="id")
     */
    protected $eikenLevel;

    /**
     * @return int
     */
    public function getEikenLevelId()
    {
        return $this->eikenLevelId;
    }

    /**
     * @param int $eikenLevelId
     */
    public function setEikenLevelId($eikenLevelId)
    {
        $this->eikenLevelId = $eikenLevelId;
    }

    /**
     * @return int
     */
    public function getPaymentInfoId()
    {
        return $this->paymentInfoId;
    }

    /**
     * @param int $paymentInfoId
     */
    public function setPaymentInfoId($paymentInfoId)
    {
        $this->paymentInfoId = $paymentInfoId;
    }

    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
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
    public function getProductName()
    {
        return $this->productName;
    }

    /**
     * @param string $productName
     */
    public function setProductName($productName)
    {
        $this->productName = $productName;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getReceiptNo()
    {
        return $this->receiptNo;
    }

    /**
     * @param string $receiptNo
     */
    public function setReceiptNo($receiptNo)
    {
        $this->receiptNo = $receiptNo;
    }

    /**
     * @return string
     */
    public function getOrderResultCode()
    {
        return $this->orderResultCode;
    }

    /**
     * @param string $orderResultCode
     */
    public function setOrderResultCode($orderResultCode)
    {
        $this->orderResultCode = $orderResultCode;
    }

    /**
     * @return string
     */
    public function getOrderResultInfo()
    {
        return $this->orderResultInfo;
    }

    /**
     * @param string $orderResultInfo
     */
    public function setOrderResultInfo($orderResultInfo)
    {
        $this->orderResultInfo = $orderResultInfo;
    }

    /**
     * @return string
     */
    public function getPaymentStatus()
    {
        return $this->paymentStatus;
    }

    /**
     * @param string $paymentStatus
     */
    public function setPaymentStatus($paymentStatus)
    {
        $this->paymentStatus = $paymentStatus;
    }

    /**
     * @return mixed
     */
    public function getPaymentInfo()
    {
        return $this->paymentInfo;
    }

    /**
     * @param mixed $paymentInfo
     */
    public function setPaymentInfo($paymentInfo)
    {
        $this->paymentInfo = $paymentInfo;
    }

    /**
     * @return mixed
     */
    public function getEikenLevel()
    {
        return $this->eikenLevel;
    }

    /**
     * @param mixed $eikenLevel
     */
    public function setEikenLevel($eikenLevel)
    {
        $this->eikenLevel = $eikenLevel;
    }
    /* Getter and Setter */

}