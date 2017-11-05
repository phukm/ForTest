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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\ApplyEikenOrgDetailsRepository")
 * @ORM\Table(name="ApplyEikenOrgDetails")
 */
class ApplyEikenOrgDetails extends Common
{

    /* Foreing key */

    /* Property */
    /**
     * @ORM\Column(type="integer", name ="ApplyEikenOrgId", nullable=true)
     *
     * @var integer
     */
    protected $applyEikenOrgId;

    /**
     * @ORM\ManyToOne(targetEntity="ApplyEikenOrg")
     * @ORM\JoinColumn(name="ApplyEikenOrgId", referencedColumnName="id")
     */
    protected $applyEikenOrg;

    /**
     * @ORM\Column(type="integer", name ="Lev1", nullable=true)
     *
     * @var integer
     */
    protected $lev1;

    /**
     * @ORM\Column(type="integer", name ="PreLev1", nullable=true)
     *
     * @var integer
     */
    protected $preLev1;

    /**
     * @ORM\Column(type="integer", name ="Lev2", nullable=true)
     *
     * @var integer
     */
    protected $lev2;

    /**
     * @ORM\Column(type="integer", name ="PreLev2", nullable=true)
     *
     * @var integer
     */
    protected $preLev2;

    /**
     * @ORM\Column(type="integer", name ="Lev3", nullable=true)
     *
     * @var integer
     */
    protected $lev3;

    /**
     * @ORM\Column(type="integer", name ="Lev4", nullable=true)
     *
     * @var integer
     */
    protected $lev4;

    /**
     * @ORM\Column(type="integer", name ="Lev5", nullable=true)
     *
     * @var integer
     */
    protected $lev5;

    /**
     * @ORM\Column(type="decimal", name ="PriceLev1", nullable=true)
     *
     * @var decimal
     */
    protected $priceLev1;

    /**
     * @ORM\Column(type="decimal", name ="PricePreLev1", nullable=true)
     *
     * @var decimal
     */
    protected $pricePreLev1;

    /**
     * @ORM\Column(type="decimal", name ="PriceLev2", nullable=true)
     *
     * @var decimal
     */
    protected $priceLev2;

    /**
     * @ORM\Column(type="decimal", name ="PricePreLev2", nullable=true)
     *
     * @var decimal
     */
    protected $pricePreLev2;

    /**
     * @ORM\Column(type="decimal", name ="PriceLev3", nullable=true)
     *
     * @var decimal
     */
    protected $priceLev3;

    /**
     * @ORM\Column(type="decimal", name ="PriceLev4", nullable=true)
     *
     * @var decimal
     */
    protected $priceLev4;

    /**
     * @ORM\Column(type="decimal", name ="PriceLev5", nullable=true)
     *
     * @var decimal
     */
    protected $priceLev5;

    /**
     * @ORM\Column(type="integer", name ="DateExamLev2", nullable=true)
     *
     * @var integer
     */
    protected $dateExamLev2;

    /**
     * @ORM\Column(type="integer", name ="DateExamPreLev2", nullable=true)
     *
     * @var integer
     */
    protected $dateExamPreLev2;

    /**
     * @ORM\Column(type="integer", name ="DateExamLev3", nullable=true)
     *
     * @var integer
     */
    protected $dateExamLev3;

    /**
     * @ORM\Column(type="integer", name ="DateExamLev4", nullable=true)
     *
     * @var integer
     */
    protected $dateExamLev4;

    /**
     * @ORM\Column(type="integer", name ="DateExamLev5", nullable=true)
     *
     * @var integer
     */
    protected $dateExamLev5;

    /**
     * @ORM\Column(type="smallint", name="HallType", nullable=true)
     */
    protected $hallType;

    /**
     * @ORM\Column(type="integer", name ="OldPreLev2", nullable=true)
     *
     * @var integer
     */
    protected $oldPreLev2;
    /**
     * @ORM\Column(type="integer", name ="OldLev2", nullable=true)
     *
     * @var integer
     */
    protected $oldLev2;
    /**
     * @ORM\Column(type="integer", name ="OldLev3", nullable=true)
     *
     * @var integer
     */
    protected $oldLev3;
    /**
     * @ORM\Column(type="integer", name ="OldLev4", nullable=true)
     *
     * @var integer
     */
    protected $oldLev4;
    /**
     * @ORM\Column(type="integer", name ="OldLev5", nullable=true)
     *
     * @var integer
     */
    protected $oldLev5;
        /**
     * @ORM\Column(type="integer", name ="DiscountLev1", nullable=true)
     *
     * @var integer
     */
    protected $discountLev1;
    /**
     * @ORM\Column(type="integer", name ="DiscountPreLev1", nullable=true)
     *
     * @var integer
     */
    protected $discountPreLev1;
    /**
     * @ORM\Column(type="integer", name ="DiscountLev2", nullable=true)
     *
     * @var integer
     */
    protected $discountLev2;
    /**
     * @ORM\Column(type="integer", name ="DiscountPreLev2", nullable=true)
     *
     * @var integer
     */
    protected $discountPreLev2;
    /**
     * @ORM\Column(type="integer", name ="DiscountLev3", nullable=true)
     *
     * @var integer
     */
    protected $discountLev3;
    /**
     * @ORM\Column(type="integer", name ="DiscountLev4", nullable=true)
     *
     * @var integer
     */
    protected $discountLev4;

        /**
     * @ORM\Column(type="integer", name ="DiscountLev5", nullable=true)
     *
     * @var integer
     */
    protected $discountLev5;

    /**
     *
     * @return int
     */
    public function getApplyEikenOrgId()
    {
        return $this->applyEikenOrgId;
    }

    /**
     *
     * @param int $applyEikenOrgId
     */
    public function setApplyEikenOrgId($applyEikenOrgId)
    {
        $this->applyEikenOrgId = $applyEikenOrgId;
    }

    /**
     *
     * @return mixed
     */
    public function getApplyEikenOrg()
    {
        return $this->applyEikenOrg;
    }

    /**
     *
     * @param mixed $applyEikenOrg
     */
    public function setApplyEikenOrg($applyEikenOrg)
    {
        $this->applyEikenOrg = $applyEikenOrg;
    }

    /**
     *
     * @return mixed
     */
    public function getEikenSchedule()
    {
        return $this->eikenSchedule;
    }

    /**
     *
     * @param mixed $eikenSchedule
     */
    public function setEikenSchedule($eikenSchedule)
    {
        $this->eikenSchedule = $eikenSchedule;
    }

    /**
     *
     * @return int
     */
    public function getLev1()
    {
        return $this->lev1;
    }

    /**
     *
     * @param int $lev1
     */
    public function setLev1($lev1)
    {
        $this->lev1 = $lev1;
    }

    /**
     *
     * @return int
     */
    public function getPreLev1()
    {
        return $this->preLev1;
    }

    /**
     *
     * @param int $preLev1
     */
    public function setPreLev1($preLev1)
    {
        $this->preLev1 = $preLev1;
    }

    /**
     *
     * @return int
     */
    public function getLev2()
    {
        return $this->lev2;
    }

    /**
     *
     * @param int $lev2
     */
    public function setLev2($lev2)
    {
        $this->lev2 = $lev2;
    }

    /**
     *
     * @return int
     */
    public function getPreLev2()
    {
        return $this->preLev2;
    }

    /**
     *
     * @param int $preLev2
     */
    public function setPreLev2($preLev2)
    {
        $this->preLev2 = $preLev2;
    }

    /**
     *
     * @return int
     */
    public function getLev3()
    {
        return $this->lev3;
    }

    /**
     *
     * @param int $lev3
     */
    public function setLev3($lev3)
    {
        $this->lev3 = $lev3;
    }

    /**
     *
     * @return int
     */
    public function getLev4()
    {
        return $this->lev4;
    }

    /**
     *
     * @param int $lev4
     */
    public function setLev4($lev4)
    {
        $this->lev4 = $lev4;
    }

    /**
     *
     * @return int
     */
    public function getLev5()
    {
        return $this->lev5;
    }

    /**
     *
     * @param int $lev5
     */
    public function setLev5($lev5)
    {
        $this->lev5 = $lev5;
    }

    /**
     *
     * @return decimal
     */
    public function getPriceLev1()
    {
        return $this->priceLev1;
    }

    /**
     *
     * @param decimal $priceLev1
     */
    public function setPriceLev1($priceLev1)
    {
        $this->priceLev1 = $priceLev1;
    }

    /**
     *
     * @return decimal
     */
    public function getPricePreLev1()
    {
        return $this->pricePreLev1;
    }

    /**
     *
     * @param decimal $pricePreLev1
     */
    public function setPricePreLev1($pricePreLev1)
    {
        $this->pricePreLev1 = $pricePreLev1;
    }

    /**
     *
     * @return decimal
     */
    public function getPriceLev2()
    {
        return $this->priceLev2;
    }

    /**
     *
     * @param decimal $PriceLev2
     */
    public function setPriceLev2($PriceLev2)
    {
        $this->priceLev2 = $PriceLev2;
    }

    /**
     *
     * @return decimal
     */
    public function getPricePreLev2()
    {
        return $this->pricePreLev2;
    }

    /**
     *
     * @param decimal $pricePreLev2
     */
    public function setPricePreLev2($pricePreLev2)
    {
        $this->pricePreLev2 = $pricePreLev2;
    }

    /**
     *
     * @return decimal
     */
    public function getPriceLev3()
    {
        return $this->priceLev3;
    }

    /**
     *
     * @param decimal $PriceLev3
     */
    public function setPriceLev3($PriceLev3)
    {
        $this->priceLev3 = $PriceLev3;
    }

    /**
     *
     * @return decimal
     */
    public function getPriceLev4()
    {
        return $this->priceLev4;
    }

    /**
     *
     * @param decimal $PriceLev4
     */
    public function setPriceLev4($PriceLev4)
    {
        $this->priceLev4 = $PriceLev4;
    }

    /**
     *
     * @return decimal
     */
    public function getPriceLev5()
    {
        return $this->priceLev5;
    }

    /**
     *
     * @param decimal $PriceLev5
     */
    public function setPriceLev5($PriceLev5)
    {
        $this->priceLev5 = $PriceLev5;
    }

    /**
     *
     * @return int
     */
    public function getDateExamLev2()
    {
        return $this->dateExamLev2;
    }

    /**
     *
     * @param int $DateExamLev2
     */
    public function setDateExamLev2($DateExamLev2)
    {
        $this->dateExamLev2 = $DateExamLev2;
    }

    /**
     *
     * @return int
     */
    public function getDateExamPreLev2()
    {
        return $this->dateExamPreLev2;
    }

    /**
     *
     * @param int $DateExamPreLev2
     */
    public function setDateExamPreLev2($DateExamPreLev2)
    {
        $this->dateExamPreLev2 = $DateExamPreLev2;
    }

    /**
     *
     * @return int
     */
    public function getDateExamLev3()
    {
        return $this->dateExamLev3;
    }

    /**
     *
     * @param int $DateExamLev3
     */
    public function setDateExamLev3($DateExamLev3)
    {
        $this->dateExamLev3 = $DateExamLev3;
    }

    /**
     *
     * @return int
     */
    public function getDateExamLev4()
    {
        return $this->dateExamLev4;
    }

    /**
     *
     * @param int $DateExamLev4
     */
    public function setDateExamLev4($DateExamLev4)
    {
        $this->dateExamLev4 = $DateExamLev4;
    }

    /**
     *
     * @return int
     */
    public function getDateExamLev5()
    {
        return $this->dateExamLev5;
    }

    /**
     *
     * @param int $DateExamLev5
     */
    public function setDateExamLev5($DateExamLev5)
    {
        $this->dateExamLev5 = $DateExamLev5;
    }

    /**
     *
     * @return mixed
     */
    public function getHallType()
    {
        return $this->hallType;
    }

    /**
     *
     * @param mixed $hallType
     */
    public function setHallType($hallType)
    {
        $this->hallType = $hallType;
    }

    /**
     * @return int
     */
    public function getOldPreLev2()
    {
        return $this->oldPreLev2;
    }

    /**
     * @param int $oldPreLev2
     */
    public function setOldPreLev2($oldPreLev2)
    {
        $this->oldPreLev2 = $oldPreLev2;
    }

    /**
     * @return int
     */
    public function getOldLev2()
    {
        return $this->oldLev2;
    }

    /**
     * @param int $oldLev2
     */
    public function setOldLev2($oldLev2)
    {
        $this->oldLev2 = $oldLev2;
    }

    /**
     * @return int
     */
    public function getOldLev3()
    {
        return $this->oldLev3;
    }

    /**
     * @param int $oldLev3
     */
    public function setOldLev3($oldLev3)
    {
        $this->oldLev3 = $oldLev3;
    }

    /**
     * @return int
     */
    public function getOldLev4()
    {
        return $this->oldLev4;
    }

    /**
     * @param int $oldLev4
     */
    public function setOldLev4($oldLev4)
    {
        $this->oldLev4 = $oldLev4;
    }

    /**
     * @return int
     */
    public function getOldLev5()
    {
        return $this->oldLev5;
    }

    /**
     * @param int $oldLev5
     */
    public function setOldLev5($oldLev5)
    {
        $this->oldLev5 = $oldLev5;
    }
    
    function getDiscountLev1() {
        return $this->discountLev1;
    }

    function getDiscountPreLev1() {
        return $this->discountPreLev1;
    }

    function getDiscountLev2() {
        return $this->discountLev2;
    }

    function getDiscountPreLev2() {
        return $this->discountPreLev2;
    }

    function getDiscountLev3() {
        return $this->discountLev3;
    }

    function getDiscountLev5() {
        return $this->discountLev5;
    }

    function setDiscountLev1($discountLev1) {
        $this->discountLev1 = $discountLev1;
    }

    function setDiscountPreLev1($discountPreLev1) {
        $this->discountPreLev1 = $discountPreLev1;
    }

    function setDiscountLev2($discountLev2) {
        $this->discountLev2 = $discountLev2;
    }

    function setDiscountPreLev2($discountPreLev2) {
        $this->discountPreLev2 = $discountPreLev2;
    }

    function setDiscountLev3($discountLev3) {
        $this->discountLev3 = $discountLev3;
    }

    function setDiscountLev5($discountLev5) {
        $this->discountLev5 = $discountLev5;
    }
    function getDiscountLev4() {
        return $this->discountLev4;
    }

    function setDiscountLev4($discountLev4) {
        $this->discountLev4 = $discountLev4;
    }






}
