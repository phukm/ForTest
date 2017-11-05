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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\DistrictRepository")
 * @ORM\Table(name="District")
 */
class District extends Common
{
    /**
     * Foreing key reference to City
     * @ORM\Column(type="integer", name="CityId", nullable=true)
     *
     * @var integer
     */
    protected $cityId;
    /**
     * @ORM\Column(type="string", name="Code", length=50, nullable=true)
     *
     * @var string
     */
    protected $code;

    /**
     * @ORM\Column(type="string", name="Name", length=255, nullable=true)
     *
     * @var string
     */
    protected $name;
    /**
     * @ORM\Column(type="boolean", name="KyuOneFirstTime",nullable=true,options={"default":0})
     *
     * @var boolean
     */
    protected $kyuOneFirstTime;
    /**
     * @ORM\Column(type="boolean", name="KyuOneSecondTime", nullable=true,options={"default":0})
     *
     * @var boolean
     */
    protected $kyuOneSecondTime;
    /**
     * @ORM\Column(type="boolean", name="KyuPreOneFirstTime",nullable=true,options={"default":0})
     *
     * @var boolean
     */
    protected $kyuPreOneFirstTime;
    /**
     * @ORM\Column(type="boolean", name="KyuPreOneSecondTime", nullable=true,options={"default":0})
     *
     * @var boolean
     */
    protected $kyuPreOneSecondTime;
    /**
     * @ORM\Column(type="boolean", name="KyuPreTwoFirstTime",nullable=true,options={"default":0})
     *
     * @var boolean
     */
    protected $kyuPreTwoFirstTime;
    /**
     * @ORM\Column(type="boolean", name="KyuPreTwoSecondTime", nullable=true,options={"default":0})
     *
     * @var boolean
     */
    protected $kyuPreTwoSecondTime;
    /**
     * @ORM\Column(type="boolean", name="KyuTwoFirstTime",nullable=true,options={"default":0})
     *
     * @var boolean
     */
    protected $kyuTwoFirstTime;
    /**
     * @ORM\Column(type="boolean", name="KyuTwoSecondTime", nullable=true,options={"default":0})
     *
     * @var boolean
     */
    protected $kyuTwoSecondTime;
    /**
     * @ORM\Column(type="boolean", name="KyuThreeFirstTime",nullable=true,options={"default":0})
     *
     * @var boolean
     */
    protected $kyuThreeFirstTime;
    /**
     * @ORM\Column(type="boolean", name="KyuThreeSecondTime", nullable=true,options={"default":0})
     *
     * @var boolean
     */
    protected $kyuThreeSecondTime;
    /**
     * @ORM\Column(type="boolean", name="KyuFourFirstTime",nullable=true,options={"default":0})
     *
     * @var boolean
     */
    protected $kyuFourFirstTime;
    /**
     * @ORM\Column(type="boolean", name="KyuFourSecondTime", nullable=true,options={"default":0})
     *
     * @var boolean
     */
    protected $kyuFourSecondTime;
    /**
     * @ORM\Column(type="boolean", name="KyuFiveFirstTime",nullable=true,options={"default":0})
     *
     * @var boolean
     */
    protected $kyuFiveFirstTime;
    /**
     * @ORM\Column(type="boolean", name="KyuFiveSecondTime", nullable=true,options={"default":0})
     *
     * @var boolean
     */
    protected $kyuFiveSecondTime;
    /**
     * @ORM\Column(type="boolean", name="ForHallType",nullable=true,options={"default":0})
     *
     * @var boolean
     */
    protected $forHallType;

    /**
     * @return int
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     * @param int $cityId
     */
    public function setCityId($cityId)
    {
        $this->cityId = $cityId;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
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
     * @return boolean
     */
    public function getKyuOneFirstTime()
    {
        return $this->kyuOneFirstTime;
    }

    /**
     * @param boolean $kyuOneFirstTime
     */
    public function setKyuOneFirstTime($kyuOneFirstTime)
    {
        $this->kyuOneFirstTime = $kyuOneFirstTime;
    }

    /**
     * @return boolean
     */
    public function getKyuOneSecondTime()
    {
        return $this->kyuOneSecondTime;
    }

    /**
     * @param boolean $kyuOneSecondTime
     */
    public function setKyuOneSecondTime($kyuOneSecondTime)
    {
        $this->kyuOneSecondTime = $kyuOneSecondTime;
    }

    /**
     * @return boolean
     */
    public function getKyuPreOneFirstTime()
    {
        return $this->kyuPreOneFirstTime;
    }

    /**
     * @param boolean $kyuPreOneFirstTime
     */
    public function setKyuPreOneFirstTime($kyuPreOneFirstTime)
    {
        $this->kyuPreOneFirstTime = $kyuPreOneFirstTime;
    }

    /**
     * @return boolean
     */
    public function getKyuPreOneSecondTime()
    {
        return $this->kyuPreOneSecondTime;
    }

    /**
     * @param boolean $kyuPreOneSecondTime
     */
    public function setKyuPreOneSecondTime($kyuPreOneSecondTime)
    {
        $this->kyuPreOneSecondTime = $kyuPreOneSecondTime;
    }

    /**
     * @return boolean
     */
    public function getKyuPreTwoFirstTime()
    {
        return $this->kyuPreTwoFirstTime;
    }

    /**
     * @param boolean $kyuPreTwoFirstTime
     */
    public function setKyuPreTwoFirstTime($kyuPreTwoFirstTime)
    {
        $this->kyuPreTwoFirstTime = $kyuPreTwoFirstTime;
    }

    /**
     * @return boolean
     */
    public function getKyuPreTwoSecondTime()
    {
        return $this->kyuPreTwoSecondTime;
    }

    /**
     * @param boolean $kyuPreTwoSecondTime
     */
    public function setKyuPreTwoSecondTime($kyuPreTwoSecondTime)
    {
        $this->kyuPreTwoSecondTime = $kyuPreTwoSecondTime;
    }
    /**
     * @return boolean
     */
    public function getKyuTwoFirstTime()
    {
        return $this->kyuTwoFirstTime;
    }

    /**
     * @param boolean $kyuTwoFirstTime
     */
    public function setKyuTwoFirstTime($kyuTwoFirstTime)
    {
        $this->kyuTwoFirstTime = $kyuTwoFirstTime;
    }

    /**
     * @return boolean
     */
    public function getKyuTwoSecondTime()
    {
        return $this->kyuTwoSecondTime;
    }

    /**
     * @param boolean $kyuTwoSecondTime
     */
    public function setKyuTwoSecondTime($kyuTwoSecondTime)
    {
        $this->kyuTwoSecondTime = $kyuTwoSecondTime;
    }

    /**
     * @return boolean
     */
    public function getKyuThreeFirstTime()
    {
        return $this->kyuThreeFirstTime;
    }

    /**
     * @param boolean $kyuThreeFirstTime
     */
    public function setKyuThreeFirstTime($kyuThreeFirstTime)
    {
        $this->kyuThreeFirstTime = $kyuThreeFirstTime;
    }

    /**
     * @return boolean
     */
    public function getKyuThreeSecondTime()
    {
        return $this->kyuThreeSecondTime;
    }

    /**
     * @param boolean $kyuThreeSecondTime
     */
    public function setKyuThreeSecondTime($kyuThreeSecondTime)
    {
        $this->kyuThreeSecondTime = $kyuThreeSecondTime;
    }

    /**
     * @return boolean
     */
    public function getKyuFourFirstTime()
    {
        return $this->kyuFourFirstTime;
    }

    /**
     * @param boolean $kyuFourFirstTime
     */
    public function setKyuFourFirstTime($kyuFourFirstTime)
    {
        $this->kyuFourFirstTime = $kyuFourFirstTime;
    }

    /**
     * @return boolean
     */
    public function getKyuFourSecondTime()
    {
        return $this->kyuFourSecondTime;
    }

    /**
     * @param boolean $kyuFourSecondTime
     */
    public function setKyuFourSecondTime($kyuFourSecondTime)
    {
        $this->kyuFourSecondTime = $kyuFourSecondTime;
    }

    /**
     * @return boolean
     */
    public function getKyuFiveFirstTime()
    {
        return $this->kyuFiveFirstTime;
    }

    /**
     * @param boolean $kyuFiveFirstTime
     */
    public function setKyuFiveFirstTime($kyuFiveFirstTime)
    {
        $this->kyuFiveFirstTime = $kyuFiveFirstTime;
    }

    /**
     * @return boolean
     */
    public function getKyuFiveSecondTime()
    {
        return $this->kyuFiveSecondTime;
    }

    /**
     * @param boolean $kyuFiveSecondTime
     */
    public function setKyuFiveSecondTime($kyuFiveSecondTime)
    {
        $this->kyuFiveSecondTime = $kyuFiveSecondTime;
    }

    /**
     * @return boolean
     */
    public function getForHallType()
    {
        return $this->forHallType;
    }

    /**
     * @param boolean $forHallType
     */
    public function setForHallType($forHallType)
    {
        $this->forHallType = $forHallType;
    }

}