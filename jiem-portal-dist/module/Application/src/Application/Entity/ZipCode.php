<?php
/**
 * Dantai Portal (http://dantai.com.jp/)
 *
 * @link      https://fhn-svn.fsoft.com.vn/svn/FSU1.GNC.JIEM-Portal/trunk/Development/SourceCode for the source repository
 * @copyright Copyright (c) 2015 FPT-Software. (http://www.fpt-software.com)
 */
namespace Application\Entity;

use Application\Entity\Common;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ZipCode")
 */
class ZipCode extends Common
{

    /**
     * @ORM\Column(type="string", name="ZipCode", length=8, nullable=true)
     */
    protected $zipCode;

    /**
     * @ORM\Column(type="string", name="CityName", length=500, nullable=true)
     */
    protected $cityName;

    /**
     * @ORM\Column(type="string", name="DistrictName", length=500, nullable=true)
     */
    protected $districtName;
    
    /**
     * @ORM\Column(type="string", name="Address", length=500, nullable=true)
     */
    protected $address;
    
    function getZipCode() {
        return $this->zipCode;
    }

    function getCityName() {
        return $this->cityName;
    }

    function getDistrictName() {
        return $this->districtName;
    }

    function getAddress() {
        return $this->address;
    }
    
    public function toArray($infoOnly = false){
        if($infoOnly){
            return array(
            'zipCode' => $this->zipCode,
            'cityName' => $this->cityName,
            'districtName' => $this->districtName,
            'address' => $this->address
            );
        }
        return get_object_vars($this);
    }

}
