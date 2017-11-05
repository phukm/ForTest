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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\GoalPassRepository")
 * @ORM\Table(name="GoalPass")
 */
class GoalPass extends Common
{

    /* Foreing key */
    /**
     * Foreing key reference to City
     * @ORM\Column(type="integer", name="CityId", nullable=true)
     *
     * @var integer
     */
    protected $cityId;

    /**
     * @ORM\Column(type="string", name="OrganizationCode", length=50, nullable=true)
     *
     * @var string
     */
    protected $organizationCode;
    /**
     *
     * @ORM\Column(type="integer", name="EikenLevelId", nullable=true)
     *
     * @var integer
     */
    protected $eikenLevelId;
    /**
     *
     * @ORM\Column(type="string", name="SchoolYearCode", nullable=true)
     *
     * @var string
     */
    protected $schoolYearCode;

    /* Property */
    /**
     *
     * @ORM\Column(type="integer", name="Year", nullable=true)
     *
     * @var integer
     */
    protected $year;

    /**
     *
     * @ORM\Column(type="integer", name="NumberPupil", nullable=true)
     *
     * @var integer
     */
    protected $numberPupil;

    /**
     *
     * @ORM\Column(type="integer", name="NumberPass", nullable=true)
     *
     * @var integer
     */
    protected $numberPass;

    /**
     * @ORM\Column(type="decimal", name="RatePass", precision=5, scale=2, nullable=true)
     *
     * @var decimal
     */
    protected $ratePass;

    /* Relationship */
    /**
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumn(name="CityId", referencedColumnName="id")
     */
    protected $city;

    /**
     * @ORM\ManyToOne(targetEntity="EikenLevel")
     * @ORM\JoinColumn(name="EikenLevelId", referencedColumnName="id")
     */
    protected $eikenLevel;

    /**
     *
     * @return int
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     *
     * @param int $cityId
     */
    public function setCityId($cityId)
    {
        $this->cityId = $cityId;
    }
    /**
     * @return string
     */
    public function getOrganizationCode()
    {
        return $this->organizationCode;
    }

    /**
     * @param string $organizationCode
     */
    public function setOrganizationCode($organizationCode)
    {
        $this->organizationCode = $organizationCode;
    }

    /**
     *
     * @return int
     */
    public function getEikenLevelId()
    {
        return $this->eikenLevelId;
    }

    /**
     *
     * @param int $eikenLevelId
     */
    public function setEikenLevelId($eikenLevelId)
    {
        $this->eikenLevelId = $eikenLevelId;
    }

    /**
     *
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     *
     * @param int $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     *
     * @return int
     */
    public function getNumberPupil()
    {
        return $this->numberPupil;
    }

    /**
     *
     * @param int $numberPupil
     */
    public function setNumberPupil($numberPupil)
    {
        $this->numberPupil = $numberPupil;
    }

    /**
     *
     * @return int
     */
    public function getNumberPass()
    {
        return $this->numberPass;
    }

    /**
     *
     * @param int $numberPass
     */
    public function setNumberPass($numberPass)
    {
        $this->numberPass = $numberPass;
    }

    /**
     *
     * @return decimal
     */
    public function getRatePass()
    {
        return $this->ratePass;
    }

    /**
     *
     * @param decimal $ratePass
     */
    public function setRatePass($ratePass)
    {
        $this->ratePass = $ratePass;
    }

    /**
     *
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     *
     * @param mixed $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     *
     * @return mixed
     */
    public function getEikenLevel()
    {
        return $this->eikenLevel;
    }

    /**
     *
     * @param mixed $eikenLevel
     */
    public function setEikenLevel($eikenLevel)
    {
        $this->eikenLevel = $eikenLevel;
    }
    /**
     *
     * @return string
     */
    public function getSchoolYearCode()
    {
        return $this->schoolYearCode;
    }

    /**
     *
     * @param int $schoolYearCode
     */
    public function setSchoolYearCode($schoolYearCode)
    {
        $this->schoolYearCode = $schoolYearCode;
    }

    /* Getter and Setter */
}