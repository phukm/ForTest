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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\RetrieveBillingInfoRepository")
 * @ORM\Table(name="RetrieveBillingInfo")
 */
class RetrieveBillingInfo extends Common
{

    /* Foreing key */
    /**
     * Foreing key reference to PaymentInfo
     * @ORM\Column(type="integer", name="PaymentInfoId", nullable=true)
     *
     * @var integer
     */
    protected $paymentInfoId;

    /* Property */
    
    /**
     * @ORM\Column(type="integer", name="BillId", nullable=true)
     *
     * @var integer
     */
    protected $billId;

    /**
     * @ORM\Column(type="string", name="OrderID", length=50, nullable=true)
     *
     * @var string
     */
    protected $orderId;

    /**
     * @ORM\Column(type="string", name="ShopID", length=6, nullable=true)
     *
     * @var string
     */
    protected $shopId;

    /**
     * @ORM\Column(type="datetime", name="PaymentDate", nullable=true)
     */
    protected $paymentDate;

    /**
     * @ORM\Column(type="smallint", name="PaymentBy", nullable=true)
     */
    protected $paymentBy;

    /**
     * @ORM\Column(type="string", name="CvsCode", length=10, nullable=true)
     *
     * @var string
     */
    protected $cvsCode;

    /**
     * @ORM\Column(type="string", name="KssspCode", length=50, nullable=true)
     *
     * @var string
     */
    protected $kssspCode;

    /**
     * @ORM\Column(type="string", name="InputID", length=50, nullable=true)
     *
     * @var string
     */
    protected $inputId;

    /**
     * @ORM\Column(type="string", name="OrdAmount", length=10, nullable=true)
     *
     * @var string
     */
    protected $ordAmount;

    /* Relationship */
    /**
     * @ORM\ManyToOne(targetEntity="PaymentInfo")
     * @ORM\JoinColumn(name="PaymentInfoId", referencedColumnName="id")
     */
    protected $paymentInfo;

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
     * @return int
     */
    public function getBillId()
    {
        return $this->billId;
    }

    /**
     * @param int $billId
     */
    public function setBillId($billId)
    {
        $this->billId = $billId;
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
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * @param string $shopId
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;
    }

    /**
     * @return mixed
     */
    public function getPaymentDate()
    {
        return $this->paymentDate;
    }

    /**
     * @param mixed $paymentDate
     */
    public function setPaymentDate($paymentDate)
    {
        $this->paymentDate = $paymentDate;
    }

    /**
     * @return mixed
     */
    public function getPaymentBy()
    {
        return $this->paymentBy;
    }

    /**
     * @param mixed $paymentBy
     */
    public function setPaymentBy($paymentBy)
    {
        $this->paymentBy = $paymentBy;
    }

    /**
     * @return string
     */
    public function getCvsCode()
    {
        return $this->cvsCode;
    }

    /**
     * @param string $cvsCode
     */
    public function setCvsCode($cvsCode)
    {
        $this->cvsCode = $cvsCode;
    }

    /**
     * @return string
     */
    public function getKssspCode()
    {
        return $this->kssspCode;
    }

    /**
     * @param string $kssspCode
     */
    public function setKssspCode($kssspCode)
    {
        $this->kssspCode = $kssspCode;
    }

    /**
     * @return string
     */
    public function getInputId()
    {
        return $this->inputId;
    }

    /**
     * @param string $inputId
     */
    public function setInputId($inputId)
    {
        $this->inputId = $inputId;
    }

    /**
     * @return string
     */
    public function getOrdAmount()
    {
        return $this->ordAmount;
    }

    /**
     * @param string $ordAmount
     */
    public function setOrdAmount($ordAmount)
    {
        $this->ordAmount = $ordAmount;
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
    /* Getter and Setter */

}