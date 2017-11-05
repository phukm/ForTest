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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\CityRepository")
 * @ORM\Table(name="City")
 */
class City extends Common
{

    /* Foreing key */
    
    /* Property */
    /**
     * @ORM\Column(type="string", name="CityCode", length=50, nullable=true)
     *
     * @var string
     */
    protected $cityCode;

    /**
     * @ORM\Column(type="string", name="CityName", length=100, nullable=false)
     *
     * @var string
     */
    protected $cityName;
    
    /**
     * @ORM\Column(type="string", name="EikenCityCode", length=50, nullable=true)
     *
     * @var string
     */
    protected $eikenCityCode;
    
    /**
     * @return string
     */
    public function getCityName()
    {
        return $this->cityName;
    }
    
    /**
     * @param string $cityName
     */
    public function setCityName($cityName)
    {
        $this->cityName = $cityName;
    }
    
    /**
     * @return string
     */
    public function getCityCode()
    {
        return $this->cityCode;
    }
    
    /**
     * @param string $cityName
     */
    public function setCityCode($cityCode)
    {
        $this->cityCode = $cityCode;
    }
    /**
     * @return string
     */
    public function getEikenCityCode()
    {
        return $this->eikenCityCode;
    }
    
    /**
     * @param string $eikenCityCode
     */
    public function setEikenCityCode($eikenCityCode)
    {
        $this->eikenCityCode = $eikenCityCode;
    }
}