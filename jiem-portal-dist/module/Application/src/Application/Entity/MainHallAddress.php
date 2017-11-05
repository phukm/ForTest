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
 * @ORM\Table(name="MainHallAddress")
 */
class MainHallAddress extends Common
{

    /* Foreing key */
    /**
     * Foreing key reference to City
     * @ORM\Column(type="integer", name="CityId", nullable=true)
     *
     * @var integer
     */
    protected $cityId;

    /* Property */
    /**
     * @ORM\Column(type="string", name="Name", length=250, nullable=true)
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="string", name="Address", length=500, nullable=true)
     *
     * @var string
     */
    protected $address;

    /**
     * @ORM\Column(type="string", name="TelNo", length=20, nullable=true)
     *
     * @var string
     */
    protected $telNo;

    /**
     * @ORM\Column(type="string", name="Mobile", length=20, nullable=true)
     *
     * @var string
     */
    protected $mobile;

    /**
     * @ORM\Column(type="string", name="Website", length=100, nullable=true)
     *
     * @var string
     */
    protected $website;

    /**
     * @ORM\Column(type="string", name="Email", length=100, nullable=true)
     *
     * @var string
     */
    protected $email;

    /* Relationship */
    /**
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumn(name="CityId", referencedColumnName="id")
     */
    protected $city;

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
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
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
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param string $mobile
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
    }

    /**
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @param string $website
     */
    public function setWebsite($website)
    {
        $this->website = $website;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }
    /* Getter and Setter */

}