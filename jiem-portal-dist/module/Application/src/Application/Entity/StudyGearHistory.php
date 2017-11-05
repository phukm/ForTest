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
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\StudyGearHistoryRepository")
 * @ORM\Table(name="StudyGearHistory")
 */
class StudyGearHistory extends Common
{

    /* Foreing key */
    /**
     * Foreing key reference to Organization
     * @ORM\Column(type="integer", name="OrganizationId", nullable=true)
     *
     * @var integer
     */
    protected $organizationId;

    /* Property */
    /**
     *
     * @ORM\Column(type="integer", name="Total", nullable=true)
     *
     * @var integer
     */
    protected $total;

    /**
     * @ORM\Column(type="datetime", name="UpdateDate", nullable=true)
     */
    protected $updateDate;

    /* Relationship */
    /**
     * @ORM\ManyToOne(targetEntity="Organization")
     * @ORM\JoinColumn(name="OrganizationId", referencedColumnName="id")
     */
    protected $organization;

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
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param int $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return mixed
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * @param mixed $updateDate
     */
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;
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

}