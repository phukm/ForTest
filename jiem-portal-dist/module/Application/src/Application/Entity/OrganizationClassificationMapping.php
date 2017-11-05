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
 * @ORM\Table(name="OrganizationClassificationMapping")
 */
class OrganizationClassificationMapping  extends Common
{

    /* Foreing key */
    /**
     * Foreing key reference to OrganizationClassification
     * @ORM\Column(type="integer", name="OrgClassificationId",nullable=true)
     *
     * @var integer
     */
    protected $orgClassificationId;

    /**
     * Foreing key reference to Organization
     * @ORM\Column(type="integer", name="OrganizationId", nullable=true)
     *
     * @var integer
     */
    protected $organizationId;

    /* Relationship */
    /**
     * Reference to table OrganizationClassification
     * @ORM\ManyToOne(targetEntity="OrganizationClassification")
     * @ORM\JoinColumn(name="OrgClassificationId", referencedColumnName="id")
     */
    protected $organizationClassification;

    /**
     * Reference to table Organization
     * @ORM\ManyToOne(targetEntity="Organization")
     * @ORM\JoinColumn(name="OrganizationId", referencedColumnName="id")
     */
    protected $organization;

    /**
     * @return int
     */
    public function getOrgClassificationId()
    {
        return $this->orgClassificationId;
    }

    /**
     * @param int $orgClassificationId
     */
    public function setOrgClassificationId($orgClassificationId)
    {
        $this->orgClassificationId = $orgClassificationId;
    }

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
     * @return mixed
     */
    public function getOrganizationClassification()
    {
        return $this->organizationClassification;
    }

    /**
     * @param mixed $organizationClassification
     */
    public function setOrganizationClassification($organizationClassification)
    {
        $this->organizationClassification = $organizationClassification;
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

    /* Property */

}