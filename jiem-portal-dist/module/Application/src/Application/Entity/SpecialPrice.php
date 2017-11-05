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
 * @ORM\Entity
 * @ORM\Table(name="SpecialPrice")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\SpecialPriceRepository")
 */
class SpecialPrice extends Common
{

    /**
     * @ORM\Column(type="integer", name="OrganizationId", nullable=false)
     *
     * @var integer
     */
    protected $organizationId;
    /**
     * @ORM\Column(type="string", name="OrganizationNo", length=50, nullable=true)
     *
     * @var string
     */
    protected $organizationNo;
    
    /**
     * @ORM\Column(type="string", name="SchoolYearCode", length=4, nullable=true)
     * @var string
     */
    protected $schoolYearCode;
    
    /**
     * @ORM\Column(type="string", name="SchoolClassification", length=4, nullable=true)
     * @var string
     */
    protected $schoolClassification;
    
    /**
     * @ORM\Column(type="integer", name="Year",length=4, nullable=false)
     * @var integer
     */
    
    protected $year;
    /**
     * @ORM\Column(type="string", name="Kai", length=2, nullable=false)
     * @var string
     */
    protected $kai;

    /**
     * @ORM\Column(type="integer", name="HallType", nullable=false)
     *
     * @var integer
     */
    protected $hallType;

    /**
     * @ORM\Column(type="integer", name ="Lev1", nullable=true)
     *
     * @var integer
     */
    protected $lev1;

    /**
     * @ORM\Column(type="decimal", name ="PreLev1", nullable=true)
     *
     * @var integer
     */
    protected $preLev1;

    /**
     * @ORM\Column(type="decimal", name ="Lev2", nullable=true)
     *
     * @var integer
     */
    protected $lev2;

    /**
     * @ORM\Column(type="decimal", name ="PreLev2", nullable=true)
     *
     * @var integer
     */
    protected $preLev2;

    /**
     * @ORM\Column(type="decimal", name ="Lev3", nullable=true)
     *
     * @var integer
     */
    protected $lev3;

    /**
     * @ORM\Column(type="decimal", name ="Lev4", nullable=true)
     *
     * @var integer
     */
    protected $lev4;

    /**
     * @ORM\Column(type="decimal", name ="Lev5", nullable=true)
     *
     * @var integer
     */
    protected $lev5;
    
    /**
     * @ORM\Column(type="string", name="DiscountKyu", nullable=true)
     *
     * @var integer
     */
    protected $discountKyu;
    
    
    function getOrganizationNo() {
        return $this->organizationNo;
    }

    function getSchoolYearCode() {
        return $this->schoolYearCode;
    }

    function getSchoolClassification() {
        return $this->schoolClassification;
    }

    function getYear() {
        return $this->year;
    }

    function getKai() {
        return $this->kai;
    }

    function getHallType() {
        return $this->hallType;
    }

    function getLev1() {
        return $this->lev1;
    }

    function getPreLev1() {
        return $this->preLev1;
    }

    function getLev2() {
        return $this->lev2;
    }

    function getPreLev2() {
        return $this->preLev2;
    }

    function getLev3() {
        return $this->lev3;
    }

    function getLev4() {
        return $this->lev4;
    }

    function getLev5() {
        return $this->lev5;
    }
    
    function getDiscountKyu(){
        return $this->discountKyu;
    }
            
    function setOrganizationNo($organizationNo) {
        $this->organizationNo = $organizationNo;
    }

    function setSchoolYearCode($schoolYearCode) {
        $this->schoolYearCode = $schoolYearCode;
    }

    function setSchoolClassification($schoolClassification) {
        $this->schoolClassification = $schoolClassification;
    }

    function setYear($year) {
        $this->year = $year;
    }

    function setKai($kai) {
        $this->kai = $kai;
    }

    function setHallType($hallType) {
        $this->hallType = $hallType;
    }

    function setLev1($lev1) {
        $this->lev1 = $lev1;
    }

    function setPreLev1($preLev1) {
        $this->preLev1 = $preLev1;
    }

    function setLev2($lev2) {
        $this->lev2 = $lev2;
    }

    function setPreLev2($preLev2) {
        $this->preLev2 = $preLev2;
    }

    function setLev3($lev3) {
        $this->lev3 = $lev3;
    }

    function setLev4($lev4) {
        $this->lev4 = $lev4;
    }

    function setLev5($lev5) {
        $this->lev5 = $lev5;
    }
    function getOrganizationId() {
        return $this->organizationId;
    }

    function setOrganizationId($organizationId) {
        $this->organizationId = $organizationId;
    }


    
    function setDiscountKyu($discountKyu){
        $this->discountKyu = $discountKyu;
    }
}
