<?php
/**
 * Dantai Portal (http://dantai.com.jp/)
 *
 * @link      https://fhn-svn.fsoft.com.vn/svn/FSU1.GNC.JIEM-Portal/trunk/Development/SourceCode for the source repository
 * @copyright Copyright (c) 2016 FPT-Software. (http://www.fpt-software.com)
 */
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\SemiVenueRepository")
 * @ORM\Table(name="SemiVenue")
 */
class SemiVenue extends Common
{
    /**
     * @ORM\Column(type="integer", name="organizationId", nullable=false)
     *
     * @var integer
     */
    protected $organizationId;

    /**
     * @ORM\Column(type="integer", name="eikenScheduleId", nullable=false)
     *
     * @var integer
     */
    protected $eikenScheduleId;

    /**
     * @ORM\Column(type="integer", name="semiMainVenue", nullable=true)
     *
     * @var integer
     */
    protected $semiMainVenue;

    /**
     * @ORM\Column(type="integer", name="semiMainVenueTemp", nullable=true)
     *
     * @var integer
     */
    protected $semiMainVenueTemp;

    /**
     * @return int
     */
    public function getOrganizationId()
    {
        return $this->organizationId;
    }

    /**
     * @param int $organizationId
     */
    public function setOrganizationId($organizationId)
    {
        $this->organizationId = $organizationId;
    }

    /**
     * @return int
     */
    public function getEikenScheduleId()
    {
        return $this->eikenScheduleId;
    }

    /**
     * @param int $eikenScheduleId
     */
    public function setEikenScheduleId($eikenScheduleId)
    {
        $this->eikenScheduleId = $eikenScheduleId;
    }

    /**
     * @return int
     */
    public function getSemiMainVenue()
    {
        return $this->semiMainVenue;
    }

    /**
     * @param $semiMainVenue
     */
    public function setSemiMainVenue($semiMainVenue)
    {
        $this->semiMainVenue = $semiMainVenue;
    }

    /**
     * @return int
     */
    public function getSemiMainVenueTemp()
    {
        return $this->semiMainVenueTemp;
    }

    /**
     * @param int $semiMainVenueTemp
     */
    public function setSemiMainVenueTemp($semiMainVenueTemp)
    {
        $this->semiMainVenueTemp = $semiMainVenueTemp;
    }

}