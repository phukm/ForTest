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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\OrgSchoolYearRepository")
 * @ORM\Table(name="OrgSchoolYear")
 */
class OrgSchoolYear extends Common
{

    /* Foreing key */
    /**
     * Foreing key reference to Organization
     * @ORM\Column(type="integer", name="OrganizationId", nullable=true)
     *
     * @var integer
     */
    protected $organizationId;

    /**
     * Foreing key reference to SchoolYear
     * @ORM\Column(type="integer", name="SchoolYearId", nullable=true)
     *
     * @var integer
     */
    protected $schoolYearId;

    /* Property */

    /**
     * @ORM\Column(type="string", name="DisplayName", length=250, nullable=false)
     *
     * @var string
     */
    protected $displayName;

    /**
     * @ORM\Column(type="smallint", name="Ordinal")
     *
     * @var integer
     */
    protected $ordinal;

    /* Relationship */
    /**
     * @ORM\ManyToOne(targetEntity="Organization")
     * @ORM\JoinColumn(name="OrganizationId", referencedColumnName="id")
     */
    protected $organization;

    /**
     * @ORM\ManyToOne(targetEntity="SchoolYear")
     * @ORM\JoinColumn(name="SchoolYearId", referencedColumnName="id")
     */
    protected $schoolYear;

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
    public function getSchoolYearId()
    {
        return $this->schoolYearId;
    }

    /**
     * @param int $schoolYearId
     */
    public function setSchoolYearId($schoolYearId)
    {
        $this->schoolYearId = $schoolYearId;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @param string $displayName
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    }

    /**
     * @return int
     */
    public function getOrdinal()
    {
        return $this->ordinal;
    }

    /**
     * @param int $ordinal
     */
    public function setOrdinal($ordinal)
    {
        $this->ordinal = $ordinal;
    }

    /**
     * @return mixed
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param mixed $organization
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
    }

    /**
     * @return mixed
     */
    public function getSchoolYear()
    {
        return $this->schoolYear;
    }

    /**
     * @param mixed $schoolYear
     */
    public function setSchoolYear($schoolYear)
    {
        $this->schoolYear = $schoolYear;
    }
    /* Getter and Setter */

}